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

namespace Admin\Contracts\Services\Provider\ZoneStorage\Result;

use Shared\Entities\Collection;
use Shared\Entities\Exception\InvalidCollectionIterationException;
use Webmozart\Assert\Assert;

/**
 * @implements Collection<ZoneStorageResult>
 */
final class ZoneStorageCollectionResult implements Collection, \Countable
{
    /** @var array<array-key, ZoneStorageResult> */
    private array $zoneStorages = [];
    private int $key = 0;

    public function __construct(private int $totalItems)
    {
    }

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, ZoneStorageResult::class);

        $this->zoneStorages[] = $item;
        $this->totalItems++;
    }

    public function current(): ZoneStorageResult
    {
        if ($this->valid()) {
            return $this->zoneStorages[$this->key];
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
        return isset($this->zoneStorages[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    public function toArray(): iterable
    {
        return $this->zoneStorages;
    }

    public function count(): int
    {
        return $this->totalItems;
    }

    /**
     * @return array<string, string>
     */
    public function toSelect(): iterable
    {
        $select = [];
        foreach ($this->zoneStorages as $zoneStorage) {
            $select += [$zoneStorage->label->toString() => $zoneStorage->uuid->toString()];
        }

        return array_merge($select);
    }
}
