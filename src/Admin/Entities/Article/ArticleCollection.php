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

namespace Admin\Entities\Article;

use Shared\Entities\Collection;
use Shared\Entities\Exception\InvalidCollectionIterationException;
use Webmozart\Assert\Assert;

/**
 * @implements Collection<Article>
 */
final class ArticleCollection implements Collection, \Countable
{
    /** @var array<array-key, Article> */
    private array $articles = [];
    private int $key = 0;

    public function __construct(private readonly int $totalItems)
    {
    }

    public function add(object $item): void
    {
        Assert::isInstanceOf($item, Article::class);

        $this->articles[] = $item;
    }

    public function current(): Article
    {
        if ($this->valid()) {
            return $this->articles[$this->key];
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
        return isset($this->articles[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return iterable<Article>
     *
     * @codeCoverageIgnore
     */
    public function toArray(): iterable
    {
        return $this->articles;
    }

    public function count(): int
    {
        return $this->totalItems;
    }
}
