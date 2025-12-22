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

namespace Admin\Contracts\Services\Provider\Article\Result;

use Shared\Entities\Collection;
use Webmozart\Assert\Assert;

/**
 * @implements Collection<ArticleResult>
 */
final class ArticleCollectionResult implements Collection, \Countable
{
    /** @var array<array-key, ArticleResult> */
    private array $articles = [];
    private int $key = 0;

    public function __construct(private readonly int $totalItems)
    {
    }

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, ArticleResult::class);

        $this->articles[] = $item;
    }

    public function current(): ArticleResult
    {
        if ($this->valid()) {
            return $this->articles[$this->key];
        }

        // @codeCoverageIgnoreStart
        throw new \OutOfBoundsException('Invalid collection key');
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
        return isset($this->articles[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    public function toArray(): iterable
    {
        return $this->articles;
    }

    /**
     * Returns the number of items in this collection (current page).
     */
    public function count(): int
    {
        return \count($this->articles);
    }

    /**
     * Returns the total number of items in DB (before pagination).
     */
    public function totalItems(): int
    {
        return $this->totalItems;
    }
}
