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

namespace Admin\Entities;

use Shared\Entities\Collection;

final class FamilyLogCollection implements Collection
{
    /** @var array<FamilyLog> */
    private array $familyLogs = [];
    private int $key = 0;

    public function add(object $item): void
    {
        if (!$item instanceof FamilyLog) {
            throw new \InvalidArgumentException('FamilyLog expected.');
        }
        $this->familyLogs[] = $item;
    }

    public function current(): FamilyLog
    {
        return $this->familyLogs[$this->key];
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
        return isset($this->familyLogs[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return iterable<FamilyLog>
     */
    public function toArray(): iterable
    {
        return $this->familyLogs;
    }
}
