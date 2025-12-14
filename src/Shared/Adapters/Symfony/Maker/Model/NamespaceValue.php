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

namespace Shared\Adapters\Symfony\Maker\Model;

use Symfony\Component\String\AbstractString;
use Symfony\Component\String\UnicodeString;

final class NamespaceValue
{
    public static function fromString(string $namespace): self
    {
        return new self(new UnicodeString($namespace));
    }

    private function __construct(private AbstractString $namespace)
    {
    }

    public function append(string $value): self
    {
        return new self(
            $this->namespace->append('\\', $value)
        );
    }

    public function toString(): string
    {
        return $this->namespace->toString();
    }
}
