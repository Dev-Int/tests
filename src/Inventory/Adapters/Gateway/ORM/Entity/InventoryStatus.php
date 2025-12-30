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

namespace Inventory\Adapters\Gateway\ORM\Entity;

use Inventory\Entities\VO\InventoryStatus as InventoryStatusDomain;

enum InventoryStatus: string
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'inProgress';
    case REVIEW = 'review';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public static function fromDomain(InventoryStatusDomain $statusDomain): self
    {
        return match ($statusDomain) {
            InventoryStatusDomain::DRAFT => self::DRAFT,
            InventoryStatusDomain::IN_PROGRESS => self::IN_PROGRESS,
            InventoryStatusDomain::REVIEW => self::REVIEW,
            InventoryStatusDomain::COMPLETED => self::COMPLETED,
            InventoryStatusDomain::CANCELLED => self::CANCELLED,
        };
    }
}
