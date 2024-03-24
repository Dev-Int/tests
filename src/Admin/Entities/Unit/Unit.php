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

namespace Admin\Entities\Unit;

use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final class Unit
{
    private string $slug;

    public static function create(ResourceUuid $uuid, NameField $label, string $abbreviation): self
    {
        return new self($uuid, $label, $abbreviation);
    }

    private function __construct(private ResourceUuid $uuid, private NameField $label, private string $abbreviation)
    {
        $this->slug = $label->slugify();
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function label(): NameField
    {
        return $this->label;
    }

    public function abbreviation(): string
    {
        return $this->abbreviation;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function changeLabel(NameField $label, string $abbreviation): void
    {
        $this->label = $label;
        $this->abbreviation = $abbreviation;
        $this->slug = $label->slugify();
    }
}
