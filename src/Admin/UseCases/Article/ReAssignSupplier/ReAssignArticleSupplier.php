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

namespace Admin\UseCases\Article\ReAssignSupplier;

use Admin\Entities\Exception\BadFamilyLogAssignedException;
use Admin\Entities\ZoneStorage\ZoneStorageCollection;
use Admin\UseCases\Gateway\ArticleRepository;

final readonly class ReAssignArticleSupplier
{
    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    public function execute(ReAssignArticleSupplierRequest $request): ReAssignSupplierResponse
    {
        $this->checkFamilyLogs($request);

        $article = $this->articleRepository->findByUuid($request->uuid());

        $zoneStorages = new ZoneStorageCollection();
        foreach ($request->zoneStorages() as $zoneStorage) {
            $zoneStorages->add($zoneStorage);
        }

        $article->reAssignSupplier(
            $request->supplier(),
            $request->familyLog(),
            $zoneStorages
        );

        $this->articleRepository->reAssignSupplier($article);

        return new ReAssignSupplierResponse($article);
    }

    private function checkFamilyLogs(ReAssignArticleSupplierRequest $request): void
    {
        $familyLog = $request->familyLog();
        $supplierFamilyLog = $request->supplier()->familyLog();
        $checkZoneStorage = true;

        foreach ($request->zoneStorages() as $zoneStorage) {
            $checkZoneStorage = $supplierFamilyLog->isCompatible($zoneStorage->familyLog());
            if ($checkZoneStorage === false) {
                break;
            }
        }
        $check = $supplierFamilyLog->isCompatible($familyLog);

        if ($check === false || $checkZoneStorage === false) {
            throw new BadFamilyLogAssignedException($request->familyLog()->label()->toString());
        }
    }
}
