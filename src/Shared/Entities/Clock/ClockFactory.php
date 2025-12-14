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

namespace Shared\Entities\Clock;

final class ClockFactory
{
    /** @var Clock|null */
    private static $clock;

    public static function clock(): Clock
    {
        return self::$clock ?? self::$clock = new SystemClock();
    }

    public static function initialize(Clock $clock): void
    {
        self::$clock = $clock;
    }
}
