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
 * @implements Collection<Inventory>
 */
final class InventoryCollection implements Collection, \Countable
{
    /** @var array<array-key, Inventory> */
    private array $inventories = [];
    private int $key = 0;

    public function __construct(private readonly int $totalItems)
    {
    }

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, Inventory::class);

        $this->inventories[] = $item;
    }

    public function current(): Inventory
    {
        if ($this->valid()) {
            return $this->inventories[$this->key];
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
        return isset($this->inventories[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return iterable<Inventory>
     */
    public function toArray(): iterable
    {
        return $this->inventories;
    }

    public function count(): int
    {
        return $this->totalItems;
    }
}
