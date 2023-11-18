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

namespace Shared\Entities\VO;

use Cocur\Slugify\Slugify;
use Shared\Entities\Exception\StringExceeds255Characters;

final class NameField
{
    public static function fromString(string $name): self
    {
        return new self($name);
    }

    private function __construct(private readonly string $name)
    {
        if (\strlen($name) > 255) {
            throw new StringExceeds255Characters($this->name);
        }
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function slugify(): string
    {
        return (new Slugify())->slugify($this->name);
    }
}
