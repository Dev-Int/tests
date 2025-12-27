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

namespace Inventory\Adapters\Controller\Symfony\Controller\RecordRealStockForZone;

use Inventory\Entities\ReadModel\ArticleData;
use Inventory\UseCases\RecordRealStockForZone\RecordRealStockForZoneRequest;
use Shared\Entities\ResourceUuid;

final readonly class RecordRealStockForZoneApiRequest implements RecordRealStockForZoneRequest
{
    /**
     * @param array<ArticleData> $articlesData
     */
    public function __construct(
        private ResourceUuid $inventoryUuid,
        private ResourceUuid $zoneStorageUuid,
        private array $articlesData,
    ) {
    }

    public function inventoryUuid(): ResourceUuid
    {
        return $this->inventoryUuid;
    }

    public function zoneStorageUuid(): ResourceUuid
    {
        return $this->zoneStorageUuid;
    }

    /**
     * @return array<ArticleData>
     */
    public function articlesData(): array
    {
        return $this->articlesData;
    }
}
