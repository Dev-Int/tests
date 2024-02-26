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

namespace Admin\UseCases\Gateway;

use Admin\Entities\FamilyLog;
use Admin\Entities\FamilyLogCollection;
use Shared\Entities\ResourceUuid;

interface FamilyLogRepository
{
    public function save(FamilyLog $familyLog): void;

    public function exists(string $label, ?FamilyLog $parent): bool;

    public function findByUuid(ResourceUuid $uuid): FamilyLog;

    public function findBySlug(string $slug): FamilyLog;

    public function findFamilyLogsOrderingBySlug(): FamilyLogCollection;

    public function updateLabel(FamilyLog $familyLog): void;

    public function assignParent(FamilyLog $familyLog, string $uuid): void;
}
