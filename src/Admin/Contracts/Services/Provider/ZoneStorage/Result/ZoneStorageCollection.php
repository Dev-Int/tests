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
 * @implements Collection<ZoneStorage>
 */
final class ZoneStorageCollection implements Collection
{
    /** @var array<array-key, ZoneStorage> */
    private array $zoneStorages = [];
    private int $key = 0;

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, ZoneStorage::class);

        $this->zoneStorages[] = $item;
    }

    public function current(): ZoneStorage
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
