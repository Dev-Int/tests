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

namespace Admin\Adapters\Gateway\ORM\Repository;

use Admin\Adapters\Gateway\ORM\Entity\Article;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Entities\Article\Article as ArticleDomain;
use Admin\Entities\Article\ArticleCollection;
use Admin\Entities\Exception\ArticleNotFoundException;
use Admin\Entities\Exception\FamilyLogNotFoundException;
use Admin\Entities\Exception\NoArticleRegisteredException;
use Admin\Entities\Exception\SupplierNotFoundException;
use Admin\Entities\Exception\TaxNotFoundException;
use Admin\Entities\Exception\ZoneStorageNotFoundException;
use Admin\UseCases\Gateway\ArticleRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Article>
 */
final class DoctrineArticleRepository extends ServiceEntityRepository implements ArticleRepository
{
    public const ALIAS = 'article';

    public function __construct(
        ManagerRegistry $registry,
        private readonly DoctrineSupplierRepository $supplierRepository,
        private readonly DoctrineTaxRepository $taxRepository,
        private readonly DoctrineZoneStorageRepository $zoneStorageRepository,
        private readonly DoctrineFamilyLogRepository $familyLogRepository,
    ) {
        parent::__construct($registry, Article::class);
    }

    public function isExists(string $name): bool
    {
        $alias = self::ALIAS;
        $article = $this->createQueryBuilder($alias)
            ->where("{$alias}.name = :name")
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $article !== null;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasArticle(): bool
    {
        $alias = self::ALIAS;
        $count = $this->createQueryBuilder($alias)
            ->select("COUNT({$alias}.slug)")
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (!\is_int($count)) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedResultException('Integer expected!');
            // @codeCoverageIgnoreEnd
        }

        return $count > 0;
    }

    public function save(ArticleDomain $article): void
    {
        $supplier = $this->supplierRepository->find($article->supplier()->uuid()->toString());
        if (!$supplier instanceof Supplier) {
            // @codeCoverageIgnoreStart
            throw new SupplierNotFoundException($article->supplier()->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $tax = $this->taxRepository->find($article->tax()->uuid()->toString());
        if (!$tax instanceof Tax) {
            // @codeCoverageIgnoreStart
            throw new TaxNotFoundException($article->tax()->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $zoneStorages = new ArrayCollection();
        foreach ($article->zoneStorages() as $zoneStorage) {
            $zoneStorageOrm = $this->zoneStorageRepository->find($zoneStorage->uuid()->toString());
            if (!$zoneStorageOrm instanceof ZoneStorage) {
                // @codeCoverageIgnoreStart
                throw new ZoneStorageNotFoundException($zoneStorage->slug());
                // @codeCoverageIgnoreEnd
            }
            $zoneStorages->add($zoneStorageOrm);
        }
        $familyLog = $this->familyLogRepository->find($article->familyLog()->uuid()->toString());

        if (!$familyLog instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFoundException($article->familyLog()->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $this->_em->persist((new Article())->fromDomain(
            $article,
            $supplier,
            $tax,
            $zoneStorages,
            $familyLog
        ));
        $this->_em->flush();
    }

    public function renameArticle(ArticleDomain $article): void
    {
        $articleToUpdate = $this->find($article->uuid()->toString());

        if (!$articleToUpdate instanceof Article) {
            // @codeCoverageIgnoreStart
            throw new ArticleNotFoundException($article->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $articleToUpdate->setName($article->name()->toString())
            ->setSlug($article->slug())
        ;

        $this->_em->flush();
    }

    public function reAssignSupplier(ArticleDomain $article): void
    {
        // TODO: Implement reAssignSupplier() method.
    }

    public function findAllArticles(): ArticleCollection
    {
        $articles = $this->findAll();
        $collection = new ArticleCollection();

        if ($articles === []) {
            throw new NoArticleRegisteredException();
        }

        foreach ($articles as $article) {
            $collection->add($article->toDomain());
        }

        return $collection;
    }

    public function findByUuid(string $uuid): ArticleDomain
    {
        $article = $this->find($uuid);
        if (!$article instanceof Article) {
            // @codeCoverageIgnoreStart
            throw new ArticleNotFoundException($uuid);
            // @codeCoverageIgnoreEnd
        }

        return $article->toDomain();
    }
}
