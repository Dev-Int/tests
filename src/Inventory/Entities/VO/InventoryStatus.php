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

namespace Inventory\Entities\VO;

enum InventoryStatus: string
{
    public function equals(InventoryStatus $otherStatus): bool
    {
        return $this->value === $otherStatus->value;
    }
    case DRAFT = 'draft';
    case IN_PROGRESS = 'inProgress';
    case REVIEW = 'review';
    case COMPLETED = 'completed';
}
