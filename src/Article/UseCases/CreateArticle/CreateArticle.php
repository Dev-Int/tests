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

namespace Article\UseCases\CreateArticle;

use Article\Entities\Article;
use Article\Entities\Exception\ArticleAlreadyExistsException;
use Article\Entities\VO\Amount;
use Article\Entities\VO\Packaging;
use Article\UseCases\Gateway\ArticleRepository;
use Article\UseCases\Gateway\FamilyLogRepository;
use Article\UseCases\Gateway\SupplierRepository;
use Article\UseCases\Gateway\ZoneStorageRepository;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Taxes;

final class CreateArticle
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly SupplierRepository $supplierRepository,
        private readonly ZoneStorageRepository $zoneStorageRepository,
        private readonly FamilyLogRepository $familyLogRepository
    ) {
    }

    public function __invoke(CreateArticleRequest $request): CreateArticleResponse
    {
        if ($this->articleRepository->articleExists($request->name())) {
            throw new ArticleAlreadyExistsException($request->name());
        }
        $supplier = $this->supplierRepository->findById($request->supplierId());
        $zoneStorages = $this->zoneStorageRepository->findByIds($request->zoneStorageIds());
        $familyLog = $this->familyLogRepository->findById($request->familyLogName());

        $article = Article::create(
            ResourceUuid::generate(),
            NameField::fromString($request->name()),
            $supplier,
            Packaging::fromArray($request->packaging()),
            Amount::fromFloat($request->price()),
            Taxes::fromFloat($request->taxes()),
            $request->minStock(),
            $zoneStorages,
            $familyLog,
            $request->quantity()
        );

        $this->articleRepository->save($article);

        return CreateArticleResponse::create($article);
    }
}
