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

namespace Admin\UseCases\Article\CreateArticle;

use Admin\Entities\Article\Article;
use Admin\Entities\Article\VO\Packaging;
use Admin\Entities\Exception\Article\ArticleAlreadyExists;
use Admin\Entities\Exception\FamilyLog\BadFamilyLogAssigned;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Repository\ArticleRepository;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;

final readonly class CreateArticle
{
    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    public function execute(CreateArticleRequest $request): CreateArticleResponse
    {
        $isExists = $this->articleRepository->isExists($request->name());
        if ($isExists) {
            throw new ArticleAlreadyExists($request->name());
        }
        $this->checkFamilyLogs(
            $request->supplier()->familyLog(),
            $request->familyLog(),
            $request->zoneStorages()
        );

        $article = Article::create(
            ResourceUuid::generate(),
            NameField::fromString($request->name()),
            $request->supplier(),
            Packaging::fromArray($request->packaging()),
            Amount::fromInt($request->unitPrice()),
            $request->tax(),
            $request->minStock(),
            $request->zoneStorages(),
            $request->familyLog(),
            true,
            $request->quantity()
        );

        $this->articleRepository->save($article);

        return new CreateArticleResponse($article);
    }

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    private function checkFamilyLogs(
        FamilyLog $supplierFamilyLog,
        FamilyLog $familyLog,
        array $zoneStorages
    ): void {
        $checkZoneStorage = true;
        foreach ($zoneStorages as $zoneStorage) {
            $checkZoneStorage = $zoneStorage->familyLog()->isCompatible($familyLog);
            if ($checkZoneStorage === false) {
                break;
            }
        }
        $check = $supplierFamilyLog->isCompatible($familyLog);

        if ($check === false || $checkZoneStorage === false) {
            throw new BadFamilyLogAssigned($familyLog->label()->toString());
        }
    }
}
