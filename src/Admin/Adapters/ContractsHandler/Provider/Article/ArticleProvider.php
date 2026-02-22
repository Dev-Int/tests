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

namespace Admin\Adapters\ContractsHandler\Provider\Article;

use Admin\Adapters\Gateway\ORM\Provider\Article\DefaultArticleAggregatorBuilder;
use Admin\Contracts\Services\Provider\Article\ArticleAggregatorBuilder;
use Admin\Contracts\Services\Provider\Article\ArticleProvider as ArticleProviderContract;
use Admin\Contracts\Services\Provider\Article\Result\ArticleResult;
use Admin\Contracts\Services\Provider\Article\Result\PackagingLevelResult;
use Admin\Contracts\Services\Provider\Article\Result\PackagingResult;
use Admin\Contracts\Services\Provider\Exception\ArticleNotFound;
use Admin\Entities\Article\Article;
use Admin\Entities\Article\VO\Packaging;
use Admin\Entities\Unit\Unit;
use Admin\UseCases\Gateway\Finder\ArticleFinder;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\ResourceUuid;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ArticleProviderContract::class)]
final readonly class ArticleProvider implements ArticleProviderContract
{
    public function __construct(
        private ArticleFinder $finder,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function provide(ResourceUuid $uuid): ArticleResult
    {
        $article = $this->finder->findByUuid($uuid);

        if (!$article instanceof Article) {
            throw new ArticleNotFound($uuid->toString());
        }

        return new ArticleResult(
            uuid: $uuid,
            name: $article->name(),
            unitPrice: $article->unitPrice(),
            quantity: $article->quantity(),
            slug: $article->slug(),
            packaging: $this->mapPackaging($article->packaging()),
        );
    }

    public function forArticles(array $articleIds): ArticleAggregatorBuilder
    {
        return new DefaultArticleAggregatorBuilder(
            $this->entityManager,
            array_values($articleIds)
        );
    }

    private function mapPackaging(Packaging $packaging): PackagingResult
    {
        $consumerUnit = $packaging->consumerUnit();

        /** @var Unit $consumerUnitUnit */
        [$consumerUnitUnit, $consumerUnitQuantity] = $consumerUnit;

        $consumerUnitResult = new PackagingLevelResult(
            $consumerUnitUnit->label()->toString(),
            $consumerUnitUnit->abbreviation(),
            $consumerUnitQuantity,
        );

        $subPackageResult = null;
        $subPackage = $packaging->subPackage();
        if ($subPackage !== null) {
            /** @var Unit $subPackageUnit */
            [$subPackageUnit, $subPackageQuantity] = $subPackage;
            $subPackageResult = new PackagingLevelResult(
                $subPackageUnit->label()->toString(),
                $subPackageUnit->abbreviation(),
                $subPackageQuantity,
            );
        }

        $parcelResult = null;
        $parcel = $packaging->parcel();
        if ($parcel !== null) {
            /** @var Unit $parcelUnit */
            [$parcelUnit, $parcelQuantity] = $parcel;
            $parcelResult = new PackagingLevelResult(
                $parcelUnit->label()->toString(),
                $parcelUnit->abbreviation(),
                $parcelQuantity,
            );
        }

        return new PackagingResult($consumerUnitResult, $subPackageResult, $parcelResult);
    }
}
