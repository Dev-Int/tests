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

namespace Inventory\Tests\EndToEnd;

use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;

trait InventoryE2ETestTrait
{
    protected function setUpFrozenClock(): void
    {
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('today')));
    }
}
