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

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ResourceUuid implements ResourceUuidInterface
{
    private UuidInterface $uuid;

    public static function generate(): self
    {
        try {
            return new self(Uuid::uuid4());
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Cannot generate a new uuid.', 0, $exception);
        }
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @param UuidInterface $uuid
     */
    public static function fromUuid(object $uuid): self
    {
        if (!$uuid instanceof UuidInterface) {
            throw new \InvalidArgumentException('UuidInterface type excepted.');
        }

        return new self($uuid);
    }

    public static function fromString(string $uuid): self
    {
        return new self(Uuid::fromString($uuid));
    }

    private function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }
}
