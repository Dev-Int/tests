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

namespace Admin\Adapters\Gateway\ORM\Finder;

use Admin\Adapters\Gateway\ORM\Entity\Article\Article as ArticleOrm;
use Admin\Entities\Article\Article;
use Admin\UseCases\Gateway\Finder\ArticleFinder;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\ResourceUuid;

final readonly class DoctrineArticleFinder implements ArticleFinder
{
    private const string ALIAS = 'article';

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByUuid(ResourceUuid|string $uuid): ?Article
    {
        $uuidStr = $uuid instanceof ResourceUuid ? $uuid->toString() : $uuid;
        $alias = self::ALIAS;

        $articleOrm = $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from(ArticleOrm::class, $alias)
            ->leftJoin("{$alias}.packaging", 'packaging')
            ->addSelect('packaging')
            ->where("{$alias}.uuid = :uuid")
            ->setParameter('uuid', $uuidStr)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$articleOrm instanceof ArticleOrm) {
            return null;
        }

        return $articleOrm->toDomain();
    }

    /**
     * @return iterable<Article>
     */
    public function findAllArticles(): iterable
    {
        $alias = self::ALIAS;

        /** @var array<ArticleOrm> $articlesOrm */
        $articlesOrm = $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from(ArticleOrm::class, $alias)
            ->leftJoin("{$alias}.packaging", 'packaging')
            ->addSelect('packaging')
            ->getQuery()
            ->getResult()
        ;

        foreach ($articlesOrm as $articleOrm) {
            yield $articleOrm->toDomain();
        }
    }

    /**
     * @param array<string> $uuids
     *
     * @return iterable<Article>
     */
    public function findByUuids(array $uuids): iterable
    {
        if ($uuids === []) {
            return;
        }

        $alias = self::ALIAS;

        /** @var array<ArticleOrm> $articlesOrm */
        $articlesOrm = $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from(ArticleOrm::class, $alias)
            ->leftJoin("{$alias}.packaging", 'packaging')
            ->addSelect('packaging')
            ->where("{$alias}.uuid IN (:uuids)")
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getResult()
        ;

        foreach ($articlesOrm as $articleOrm) {
            yield $articleOrm->toDomain();
        }
    }
}
