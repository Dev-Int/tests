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

namespace Admin\Entities\Repository;

use Admin\Entities\Exception\FamilyLog\FamilyLogNotFound;
use Admin\Entities\Exception\FamilyLog\NoFamilyLogRegistered;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\FamilyLog\FamilyLogCollection;
use Shared\Entities\ResourceUuid;

interface FamilyLogRepository
{
    public function exists(string $label, ?FamilyLog $parent): bool;

    public function hasFamilyLog(): bool;

    /**
     * @throws FamilyLogNotFound
     */
    public function save(FamilyLog $familyLog): void;

    /**
     * @throws FamilyLogNotFound
     */
    public function updateLabel(FamilyLog $familyLog): void;

    /**
     * @throws FamilyLogNotFound
     */
    public function assignParent(FamilyLog $familyLog, string $uuid): void;

    /**
     * @throws FamilyLogNotFound
     */
    public function getByUuid(ResourceUuid $uuid): FamilyLog;

    /**
     * @throws FamilyLogNotFound
     */
    public function getByUuidWithChildren(ResourceUuid $uuid): FamilyLog;

    /**
     * @throws FamilyLogNotFound
     */
    public function getBySlug(string $slug): FamilyLog;

    /**
     * @throws NoFamilyLogRegistered
     */
    public function getFamilyLogsOrderingBySlug(): FamilyLogCollection;
}
