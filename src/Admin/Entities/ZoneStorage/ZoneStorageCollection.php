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

namespace Admin\Entities\ZoneStorage;

use Shared\Entities\Collection;
use Webmozart\Assert\Assert;

final class ZoneStorageCollection implements Collection
{
    /** @var array<ZoneStorage> */
    private array $zoneStorages = [];
    private int $key = 0;

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, ZoneStorage::class);

        $this->zoneStorages[] = $item;
    }

    public function current(): ZoneStorage
    {
        return $this->zoneStorages[$this->key];
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

    /**
     * @return iterable<ZoneStorage>
     */
    public function toArray(): iterable
    {
        return $this->zoneStorages;
    }
}
