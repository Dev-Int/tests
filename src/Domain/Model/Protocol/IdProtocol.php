<?php

declare(strict_types=1);

namespace Domain\Model\Protocol;

use Domain\Model\Protocol\Common\StringifyProtocol;

interface IdProtocol extends StringifyProtocol
{
    public static function generate(): IdProtocol;
}
