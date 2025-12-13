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

use Symfony\Component\String\UnicodeString;

final readonly class Path
{
    public static function fromString(string $path): self
    {
        return new self(new UnicodeString($path));
    }

    private function __construct(private UnicodeString $path)
    {
    }

    public function append(string ...$paths): self
    {
        $fromPath = $this->path;

        foreach ($paths as $path) {
            $fromPath = $fromPath->append('/', $path);
        }

        return new self($fromPath);
    }

    public function toString(): string
    {
        return $this->path->toString();
    }
}
