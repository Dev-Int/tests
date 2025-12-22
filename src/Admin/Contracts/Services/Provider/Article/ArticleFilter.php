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
 * Available filters for ArticleAggregatorBuilder.
 *
 * Used by other BCs to filter articles through the Provider contract.
 */
enum ArticleFilter: string
{
    case ZONE_STORAGE = 'zoneStorage';
    case FAMILY_LOG = 'familyLog';
    case SUPPLIER = 'supplier';
    case ACTIVE = 'active';
}
