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

namespace Inventory\Entities;

use Shared\Entities\Collection;
use Shared\Entities\Exception\InvalidCollectionIterationException;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Quantity;
use Webmozart\Assert\Assert;

/**
 * @implements Collection<InventoryItem>
 */
final class InventoryItemCollection implements Collection, \Countable
{
    /** @var array<array-key, InventoryItem> */
    private array $items = [];
    private int $key = 0;

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, InventoryItem::class);

        $this->items[] = $item;
    }

    public function current(): InventoryItem
    {
        if ($this->valid()) {
            return $this->items[$this->key];
        }

        // @codeCoverageIgnoreStart
        throw new InvalidCollectionIterationException();
        // @codeCoverageIgnoreEnd
    }

    public function next(): void
    {
        $this->key++;
    }

    /**
     * @codeCoverageIgnore
     */
    public function key(): int
    {
        return $this->key;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return iterable<InventoryItem>
     */
    public function toArray(): iterable
    {
        return $this->items;
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function findByArticleAndZone(ResourceUuid $articleUuid, ResourceUuid $zoneStorageUuid): ?InventoryItem
    {
        foreach ($this->items as $item) {
            if ($item->isFor($articleUuid, $zoneStorageUuid)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param array<InventoryItem> $newItems
     */
    public function replace(array $newItems): void
    {
        $indexedNewItems = [];
        foreach ($newItems as $newItem) {
            $indexedNewItems[$newItem->identifier()] = $newItem;
        }

        foreach ($this->items as $key => $item) {
            $identifier = $item->identifier();
            if (isset($indexedNewItems[$identifier])) {
                $this->items[$key] = $indexedNewItems[$identifier];
            }
        }
    }

    public function getTotalRealStockForArticle(ResourceUuid $articleUuid): Quantity
    {
        $totalMilliemes = array_reduce(
            $this->items,
            static fn (int $carry, InventoryItem $item): int => $item->isForArticle($articleUuid) ? $carry + $item->realStock()->toMilliemes() : $carry,
            0
        );

        return Quantity::fromMilliemes($totalMilliemes);
    }

    /**
     * @return array<InventoryItem>
     */
    public function filterByZone(ResourceUuid $zoneStorageUuid): array
    {
        return array_filter(
            $this->items,
            static fn (InventoryItem $item): bool => $item->isForZone($zoneStorageUuid)
        );
    }

    /**
     * @return array<ResourceUuid>
     */
    public function getArticleUuids(): array
    {
        $articles = [];
        foreach ($this->items as $item) {
            $articles[$item->article()->toString()] = $item->article();
        }

        return array_values($articles);
    }
}
