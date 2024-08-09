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

namespace Admin\Entities\Unit;

use Shared\Entities\Collection;
use Shared\Entities\Exception\InvalidCollectionIterationException;
use Webmozart\Assert\Assert;

final class UnitCollection implements Collection
{
    /** @var array<Unit> */
    private array $units = [];
    private int $key = 0;

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, Unit::class);

        $this->units[] = $item;
    }

    public function current(): Unit
    {
        if ($this->valid()) {
            return $this->units[$this->key];
        }

        // @codeCoverageIgnoreStart
        throw new InvalidCollectionIterationException();
        // @codeCoverageIgnoreEnd
    }

    public function next(): void
    {
        $this->key++;
    }

    public function key(): int
    {
        return $this->key;
    }

    public function valid(): bool
    {
        return isset($this->units[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return iterable<Unit>
     *
     * @codeCoverageIgnore
     */
    public function toArray(): iterable
    {
        return $this->units;
    }
}
