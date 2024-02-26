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

namespace Admin\Tests\DataBuilder;

use Admin\Entities\FamilyLog;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final class FamilyLogDataBuilder
{
    public const VALID_UUID = '48e50c4f-7d87-427c-bc93-c67564664266';

    private ResourceUuid $uuid;
    private NameField $label;
    private ?FamilyLog $parent = null;

    public function create(string $label): self
    {
        $this->uuid = ResourceUuid::fromString(self::VALID_UUID);
        $this->label = NameField::fromString($label);

        return $this;
    }

    public function withUuid(string $uuid): self
    {
        $this->uuid = ResourceUuid::fromString($uuid);

        return $this;
    }

    public function withParent(FamilyLog $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function build(): FamilyLog
    {
        return FamilyLog::create($this->uuid, $this->label, $this->parent);
    }
}
