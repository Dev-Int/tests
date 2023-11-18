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

namespace Article\Entities\Component;

use Shared\Entities\VO\NameField;

final class ZoneStorage
{
    private NameField $name;
    private string $slug;

    public static function create(NameField $name): self
    {
        return new self($name);
    }

    private function __construct(NameField $name)
    {
        $this->name = $name;
        $this->slug = $name->slugify();
    }

    public function renameZone(NameField $name): void
    {
        $this->name = $name;
        $this->slug = $name->slugify();
    }

    public function name(): NameField
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
