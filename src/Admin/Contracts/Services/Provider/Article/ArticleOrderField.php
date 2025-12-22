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

namespace Admin\Contracts\Services\Provider\Article;

/**
 * Available fields for ordering articles in ArticleAggregatorBuilder.
 *
 * Used by other BCs to sort articles through the Provider contract.
 */
enum ArticleOrderField: string
{
    case NAME = 'name';
    case UNIT_PRICE = 'unitPrice';
    case SLUG = 'slug';
}
