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

namespace Admin\Adapters\Gateway\Contracts\Provider\Article;

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

    public function withFilter(ArticleFilter $filter, mixed $value): self
    {
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
        $normalizedDirection = strtoupper($direction->value);
        if (!\in_array($normalizedDirection, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid order direction: %s. Allowed: ASC, DESC', $direction->value)
            );
        }

        $this->orderBy[$field->value] = $normalizedDirection;

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

        /** @var list<Article> $articles */
        $articles = $queryBuilder->getQuery()->getResult();

        foreach ($articles as $article) {
            $collection->add($this->mapToResult($article));
        }

        return $collection;
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select(self::ALIAS)
            ->from(Article::class, self::ALIAS)
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
            /** @var ResourceUuid $zoneStorageUuid */
            $zoneStorageUuid = $this->filters[ArticleFilter::ZONE_STORAGE->value];
            $queryBuilder
                ->innerJoin("{$alias}.zoneStorages", 'zs')
                ->andWhere('zs.uuid = :zoneStorageId')
                ->setParameter('zoneStorageId', $zoneStorageUuid->toString())
            ;
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

    private function countTotal(QueryBuilder $queryBuilder): int
    {
        $countQueryBuilder = clone $queryBuilder;

        return (int) $countQueryBuilder
            ->select('COUNT(DISTINCT ' . self::ALIAS . '.uuid)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function mapToResult(Article $article): ArticleResult
    {
        return new ArticleResult(
            uuid: ResourceUuid::fromString($article->uuid()),
            name: NameField::fromString($article->name()),
            unitPrice: Amount::fromCents($article->unitPrice()),
            quantity: $article->quantity(),
            slug: $article->slug(),
        );
    }
}
