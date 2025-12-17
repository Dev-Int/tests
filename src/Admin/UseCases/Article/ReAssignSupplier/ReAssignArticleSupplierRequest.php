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

use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Shared\Entities\ResourceUuid;

interface ReAssignArticleSupplierRequest
{
    public function supplier(): Supplier;

    public function familyLog(): FamilyLog;

    /**
     * @return array<ZoneStorage>
     */
    public function zoneStorages(): array;

    public function uuid(): ResourceUuid;
}
