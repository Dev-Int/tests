<?php

declare(strict_types=1);

/*
 * This file is part of the Tests package.
 *
 * (c) Dev-Int Création <info@developpement-interessant.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Admin\Adapters\Gateway\ORM\Provider\Article;

use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Contracts\Services\Provider\Article\ArticleAggregatorBuilder;
use Admin\Contracts\Services\Provider\Article\ArticleFilter;
use Admin\Contracts\Services\Provider\Article\ArticleOrderField;
use Admin\Contracts\Services\Provider\Article\Result\ArticleCollectionResult;
use Admin\Contracts\Services\Provider\Article\Result\ArticleResult;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shared\Entities\Enum\QueryOrder;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Quantity;

/**
 * Mutable article aggregator builder for query construction.
 *
 * USAGE: Single-use per query. Create a new instance via ArticleProvider::forArticles().
 *
 * ✅ $provider->forArticles([])->withFilter(...)->provideAll();
 * ❌ $builder = $provider->forArticles([]); $builder->...; $builder->...;
 *
 * @performance Le count total utilise un clone du QueryBuilder (2 requêtes SQL).
 *              Si besoin d'optimisation sur gros volumes, envisager DBAL avec
 *              COUNT(*) OVER() AS total_count (1 seule requête).
 *
 * @refactoring Si passage en DBAL, envisager architecture DTO :
 *              1. Créer ArticleSearchCriteria DTO dans UseCases
 *              2. Enrichir ArticleFinder avec findByCriteria(ArticleSearchCriteria)
 *              3. Ce Builder devient simple constructeur de DTO
 *              4. Logique DBAL isolée dans implémentation Doctrine du Finder
 */
final class DefaultArticleAggregatorBuilder implements ArticleAggregatorBuilder
{
    private const string ALIAS = 'a';

    /** @var array<string, mixed> */
    private array $filters = [];

    /** @var array<string, string> */
    private array $orderBy = [];

    private ?int $limit = null;
    private ?int $offset = null;

    /**
     * @param array<int, ResourceUuid> $articleIds
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly array $articleIds = [],
    ) {
    }

    public function withFilter(ArticleFilter $filter, array|bool|ResourceUuid $value): self
    {
        $this->validateFilterValue($filter, $value);
        $this->filters[$filter->value] = $value;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function orderBy(ArticleOrderField $field, QueryOrder $direction = QueryOrder::ASC): self
    {
        $this->orderBy[$field->value] = $direction->value;

        return $this;
    }

    public function provideAll(): ArticleCollectionResult
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        $this->applyArticleIdsFilter($queryBuilder);
        $this->applyFilters($queryBuilder);

        // Count total before pagination
        $totalCount = $this->countTotal($queryBuilder);

        // Apply ordering
        foreach ($this->orderBy as $field => $direction) {
            $queryBuilder->addOrderBy(self::ALIAS . ".{$field}", $direction);
        }

        // Apply pagination
        if ($this->offset !== null) {
            $queryBuilder->setFirstResult($this->offset);
        }
        if ($this->limit !== null) {
            $queryBuilder->setMaxResults($this->limit);
        }

        // Execute and map results
        $collection = new ArticleCollectionResult($totalCount);

        /** @var array<array{uuid: string, name:string, unitPrice: int, quantity: float|int, slug: string}> $articles */
        $articles = $queryBuilder->getQuery()->getResult();

        foreach ($articles as $article) {
            $collection->add($this->mapToResult($article));
        }

        return $collection;
    }

    /**
     * @param array<ResourceUuid>|bool|ResourceUuid $value
     */
    private function validateFilterValue(ArticleFilter $filter, array|bool|ResourceUuid $value): void
    {
        match ($filter) {
            ArticleFilter::ZONE_STORAGE,
            ArticleFilter::FAMILY_LOG,
            ArticleFilter::SUPPLIER => $this->assertResourceUuidOrArray($value),
            ArticleFilter::ACTIVE => $this->assertBoolean($value),
        };
    }

    /**
     * Validates that value is ResourceUuid or array of ResourceUuid.
     *
     * @throws \InvalidArgumentException if validation fails
     */
    private function assertResourceUuidOrArray(mixed $value): void
    {
        if ($value instanceof ResourceUuid) {
            return;
        }

        if (\is_array($value)) {
            foreach ($value as $item) {
                if (!$item instanceof ResourceUuid) {
                    throw new \InvalidArgumentException(
                        \sprintf('Expected ResourceUuid, got %s in array', get_debug_type($item))
                    );
                }
            }

            return;
        }

        throw new \InvalidArgumentException(
            \sprintf('Expected ResourceUuid or array of ResourceUuid, got %s', get_debug_type($value))
        );
    }

    private function assertBoolean(mixed $value): void
    {
        if (!\is_bool($value)) {
            throw new \InvalidArgumentException(
                \sprintf('Expected bool, got %s', get_debug_type($value))
            );
        }
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        $alias = self::ALIAS;

        return $this->entityManager
            ->createQueryBuilder()
            ->select(["{$alias}.uuid", "{$alias}.name", "{$alias}.unitPrice", "{$alias}.quantity", "{$alias}.slug"])
            ->from(Article::class, $alias)
        ;
    }

    private function applyArticleIdsFilter(QueryBuilder $queryBuilder): void
    {
        if ($this->articleIds === []) {
            return;
        }

        $uuids = array_map(
            static fn (ResourceUuid $uuid): string => $uuid->toString(),
            $this->articleIds
        );

        $queryBuilder
            ->andWhere(self::ALIAS . '.uuid IN (:articleIds)')
            ->setParameter('articleIds', $uuids)
        ;
    }

    private function applyFilters(QueryBuilder $queryBuilder): void
    {
        $alias = self::ALIAS;

        if (isset($this->filters[ArticleFilter::ZONE_STORAGE->value])) {
            $this->applyZoneStorageFilter($queryBuilder, $alias);
        }

        if (isset($this->filters[ArticleFilter::FAMILY_LOG->value])) {
            /** @var ResourceUuid $familyLogUuid */
            $familyLogUuid = $this->filters[ArticleFilter::FAMILY_LOG->value];
            $queryBuilder
                ->andWhere("{$alias}.familyLog = :familyLogId")
                ->setParameter('familyLogId', $familyLogUuid->toString())
            ;
        }

        if (isset($this->filters[ArticleFilter::SUPPLIER->value])) {
            /** @var ResourceUuid $supplierUuid */
            $supplierUuid = $this->filters[ArticleFilter::SUPPLIER->value];
            $queryBuilder
                ->andWhere("{$alias}.supplier = :supplierId")
                ->setParameter('supplierId', $supplierUuid->toString())
            ;
        }

        if (isset($this->filters[ArticleFilter::ACTIVE->value])) {
            $queryBuilder
                ->andWhere("{$alias}.active = :active")
                ->setParameter('active', $this->filters[ArticleFilter::ACTIVE->value])
            ;
        }
    }

    private function applyZoneStorageFilter(QueryBuilder $queryBuilder, string $alias): void
    {
        /** @var array<ResourceUuid>|ResourceUuid $value */
        $value = $this->filters[ArticleFilter::ZONE_STORAGE->value];

        $queryBuilder->innerJoin("{$alias}.zoneStorages", 'zs');
        $queryBuilder->addSelect('zs.uuid AS zoneStorageUuid');

        if (\is_array($value)) {
            $uuids = array_map(
                static fn (ResourceUuid $uuid): string => $uuid->toString(),
                $value
            );
            $queryBuilder
                ->andWhere('zs.uuid IN (:zoneStorageIds)')
                ->setParameter('zoneStorageIds', $uuids)
            ;
        } else {
            $queryBuilder
                ->andWhere('zs.uuid = :zoneStorageId')
                ->setParameter('zoneStorageId', $value->toString())
            ;
        }
    }

    private function countTotal(QueryBuilder $queryBuilder): int
    {
        $countQueryBuilder = clone $queryBuilder;

        return (int) $countQueryBuilder
            ->select('COUNT(DISTINCT ' . self::ALIAS . '.uuid)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param array{uuid: string, name:string, unitPrice: int, quantity: float|int, slug: string, zoneStorageUuid?: string} $article
     */
    private function mapToResult(array $article): ArticleResult
    {
        return new ArticleResult(
            uuid: ResourceUuid::fromString($article['uuid']),
            name: NameField::fromString($article['name']),
            unitPrice: Amount::fromCents($article['unitPrice']),
            quantity: Quantity::fromMilliemes((int) $article['quantity']),
            slug: $article['slug'],
            zoneStorageUuid: isset($article['zoneStorageUuid'])
                ? ResourceUuid::fromString($article['zoneStorageUuid'])
                : null,
        );
    }
}
