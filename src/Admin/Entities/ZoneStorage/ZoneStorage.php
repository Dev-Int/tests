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

namespace Admin\Entities\ZoneStorage;

use Admin\Entities\FamilyLog;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final class ZoneStorage
{
    private string $slug;

    public static function create(ResourceUuid $uuid, NameField $label, FamilyLog $familyLog): self
    {
        return new self($uuid, $label, $familyLog);
    }

    private function __construct(
        private readonly ResourceUuid $uuid,
        private NameField $label,
        private readonly FamilyLog $familyLog
    ) {
        $this->slug = $this->label->slugify();
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function label(): NameField
    {
        return $this->label;
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function changeLabel(NameField $label): void
    {
        $this->label = $label;
        $this->slug = $label->slugify();
    }
}
