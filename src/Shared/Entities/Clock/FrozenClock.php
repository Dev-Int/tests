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

final class FrozenClock implements Clock
{
    private \DateTimeImmutable $now;

    public function __construct(?\DateTimeImmutable $now = null)
    {
        $this->now = $now ?? new \DateTimeImmutable();
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    public function freeze(\DateTimeImmutable $now): self
    {
        $this->now = $now;

        return $this;
    }

    public function modify(string $dateOrDelta): self
    {
        $this->now = $this->now->modify($dateOrDelta);

        return $this;
    }
}
