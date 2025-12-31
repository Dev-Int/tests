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

namespace Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory;

/**
 * DTO for zones with unreviewed items, used in template.
 */
final readonly class ZoneWithUnreviewedItemsResult
{
    public function __construct(
        public string $uuid,
        public string $label,
    ) {
    }
}
