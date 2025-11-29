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

namespace Shared\Tests\Entities\Collection;

use Shared\Entities\Collection;
use Shared\Entities\Exception\InvalidCollectionIterationException;
use Webmozart\Assert\Assert;

/**
 * @implements Collection<Something>
 */
final class SomethingCollection implements Collection
{
    /** @var array<array-key, Something> */
    private array $collection = [];

    private int $key = 0;

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, Something::class);

        $this->collection[] = $item;
    }

    public function current(): Something
    {
        if ($this->valid()) {
            return $this->collection[$this->key];
        }

        throw new InvalidCollectionIterationException();
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
        return isset($this->collection[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return iterable<Something>
     */
    public function toArray(): iterable
    {
        return $this->collection;
    }
}
