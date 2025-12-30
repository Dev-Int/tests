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

namespace Inventory\Entities\Exception;

use Inventory\Entities\InventoryItem;
use Shared\Entities\Exception\DomainException;

final class UnreviewedDiscrepancies extends DomainException implements \JsonSerializable
{
    public const string MESSAGE = 'All discrepancies must be reviewed before completing the inventory.';

    /**
     * @param array<InventoryItem> $unreviewedItems
     */
    public function __construct(
        private readonly array $unreviewedItems
    ) {
        parent::__construct(self::MESSAGE, DomainException::INVALID_ARGUMENT_CODE);
    }

    /**
     * @return array<InventoryItem>
     */
    public function unreviewedItems(): array
    {
        return $this->unreviewedItems;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'message' => self::MESSAGE,
            'unreviewedCount' => \count($this->unreviewedItems),
            'unreviewedItems' => array_map(
                static fn (InventoryItem $item): array => [
                    'articleUuid' => $item->article()->toString(),
                    'zoneStorageUuid' => $item->zoneStorage()->toString(),
                ],
                $this->unreviewedItems
            ),
        ];
    }
}
