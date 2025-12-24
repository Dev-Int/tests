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

use Inventory\Entities\Exception\EqualOrFutureDateExpected;
use Shared\Entities\Clock\ClockFactory;

final readonly class InventoryDate
{
    public static function fromDateTimeImmutable(\DateTimeImmutable $date): self
    {
        return new self($date, validate: true);
    }

    /**
     * Reconstitute from persistence without validation.
     * Data from DB was already validated at creation time.
     */
    public static function reconstitute(\DateTimeImmutable $date): self
    {
        return new self($date, validate: false);
    }

    private function __construct(private \DateTimeImmutable $value, bool $validate = true)
    {
        if ($validate) {
            $now = ClockFactory::clock()->now();
            if ($this->value < $now->setTime(hour: 0, minute: 0)) {
                throw new EqualOrFutureDateExpected($value);
            }
        }
    }

    public function toDateTimeImmutable(): \DateTimeImmutable
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
