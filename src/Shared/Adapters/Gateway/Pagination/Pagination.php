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

namespace Shared\Adapters\Gateway\Pagination;

final class Pagination
{
    public const int DEFAULT_ITEMS_PER_PAGE = 25;
    public const int DEFAULT_PAGE = 1;
    private int $totalPages;
    private int $itemsPerPage;

    public function __construct(int $totalItems, public int $page = 1, int $itemsPerPage = 25)
    {
        $this->itemsPerPage = max($itemsPerPage, self::DEFAULT_ITEMS_PER_PAGE);
        $this->totalPages = (int) ceil($totalItems / $this->itemsPerPage);
    }

    public function itemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function totalPages(): int
    {
        return $this->totalPages;
    }
}
