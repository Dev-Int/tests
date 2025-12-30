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

namespace Admin\Entities\Event;

use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Quantity;

/**
 * Domain event raised when an article quantity falls below its minimum stock.
 */
final readonly class LowStockDetected
{
    public function __construct(
        public ResourceUuid $articleUuid,
        public NameField $articleName,
        public Quantity $currentQuantity,
        public float $minStock,
    ) {
    }
}
