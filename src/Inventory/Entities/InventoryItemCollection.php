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
use Webmozart\Assert\Assert;

/**
 * @implements Collection<InventoryItem>
 */
final class InventoryItemCollection implements Collection, \Countable
{
    /** @var array<array-key, InventoryItem> */
    private array $items = [];
    private int $key = 0;

    public function __construct(private readonly int $totalItems)
    {
    }

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
        return $this->totalItems;
    }
}
