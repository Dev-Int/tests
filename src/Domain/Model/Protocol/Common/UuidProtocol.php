<?php

namespace Domain\Model\Protocol\Common;

use Domain\Model\Protocol\IdProtocol;

interface UuidProtocol extends IdProtocol
{
    /**
     * @return static
     */
    public static function fromUuid(object $uuid);

    /**
     * @return static
     */
    public static function fromString(string $uuid);
}
