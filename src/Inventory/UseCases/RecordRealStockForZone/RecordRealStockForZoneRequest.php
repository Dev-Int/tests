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

namespace Inventory\UseCases\RecordRealStockForZone;

use Inventory\Entities\ReadModel\ArticleData;
use Shared\Entities\ResourceUuid;

interface RecordRealStockForZoneRequest
{
    public function inventoryUuid(): ResourceUuid;

    public function zoneStorageUuid(): ResourceUuid;

    /**
     * @return array<ArticleData>
     */
    public function articlesData(): array;
}
