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

namespace Admin\Entities\Supplier;

use Shared\Entities\Collection;
use Webmozart\Assert\Assert;

final class SupplierCollection implements Collection, \Countable
{
    /** @var array<Supplier>
     */
    private array $suppliers = [];
    private int $key = 0;

    public function __construct(private readonly int $totalItems)
    {
    }

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, Supplier::class);

        $this->suppliers[] = $item;
    }

    public function current(): Supplier
    {
        return $this->suppliers[$this->key];
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
        return isset($this->suppliers[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return iterable<Supplier>
     *
     * @codeCoverageIgnore
     */
    public function toArray(): iterable
    {
        return $this->suppliers;
    }

    public function count(): int
    {
        return $this->totalItems;
    }
}
