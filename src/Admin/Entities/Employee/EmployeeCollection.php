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

namespace Admin\Entities\Employee;

use Shared\Entities\Collection;
use Shared\Entities\Exception\InvalidCollectionIterationException;
use Webmozart\Assert\Assert;

/**
 * @implements Collection<Employee>
 */
final class EmployeeCollection implements Collection, \Countable
{
    /**
     * @var array<array-key, Employee>
     */
    private array $employees = [];
    private int $key = 0;

    public function __construct(private readonly int $totalItems)
    {
    }

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, Employee::class);

        $this->employees[] = $item;
    }

    public function current(): Employee
    {
        if ($this->valid()) {
            return $this->employees[$this->key];
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
        return isset($this->employees[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return array<array-key, Employee>
     *
     * @codeCoverageIgnore
     */
    public function toArray(): array
    {
        return $this->employees;
    }

    public function count(): int
    {
        return $this->totalItems;
    }
}
