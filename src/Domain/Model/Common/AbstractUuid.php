<?php

namespace Domain\Model\Common;

use Domain\Model\Protocol\Common\UuidProtocol;
use Domain\Model\Protocol\IdProtocol;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class AbstractUuid implements UuidProtocol
{
    private UuidInterface $uuid;

    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    public static function generate(): IdProtocol
    {
        try {
            return new static(Uuid::uuid4());
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Cannot generate a new uuid.', null, $exception);
        }
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }

    /**
     * @param UuidInterface $uuid
     */
    public static function fromUuid(object $uuid): UuidProtocol
    {
        if ($uuid instanceof UuidInterface) {
            throw new \InvalidArgumentException('UuidInterface type excepted.');
        }

        return new static($uuid);
    }

    /**
     * @return static
     */
    public static function fromString(string $uuid)
    {
        return new static(Uuid::fromString($uuid));
    }
}
