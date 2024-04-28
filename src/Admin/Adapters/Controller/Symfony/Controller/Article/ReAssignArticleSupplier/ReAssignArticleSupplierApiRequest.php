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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\ReAssignArticleSupplier;

use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\UseCases\Article\ReAssignSupplier\ReAssignArticleSupplierRequest;

final class ReAssignArticleSupplierApiRequest implements ReAssignArticleSupplierRequest
{
    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public function __construct(
        public Supplier $supplier,
        public FamilyLog $familyLog,
        public array $zoneStorages,
        public string $uuid
    ) {
    }

    public function supplier(): Supplier
    {
        return $this->supplier;
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    /**
     * @return array<ZoneStorage>
     */
    public function zoneStorages(): array
    {
        return $this->zoneStorages;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}
