<?php

declare(strict_types=1);

namespace Domain\Model\Protocol\Common;

interface StringifyProtocol
{
    public function toString(): string;
}
