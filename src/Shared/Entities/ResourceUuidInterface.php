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

namespace Shared\Entities;

use Ramsey\Uuid\UuidInterface;

interface ResourceUuidInterface
{
    public static function fromUuid(UuidInterface $uuid): self;

    public static function generate(): self;

    public static function fromString(string $uuid): self;

    public function toString(): string;
}
