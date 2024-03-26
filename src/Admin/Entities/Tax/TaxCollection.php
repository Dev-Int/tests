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

namespace Admin\Entities\Tax;

use Shared\Entities\Collection;
use Webmozart\Assert\Assert;

final class TaxCollection implements Collection
{
    /** @var array<Tax> */
    private array $taxes = [];

    private int $key = 0;

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, Tax::class);

        $this->taxes[] = $item;
    }

    public function current(): Tax
    {
        return $this->taxes[$this->key];
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
        return isset($this->taxes[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return iterable<Tax>
     */
    public function toArray(): iterable
    {
        return $this->taxes;
    }
}
