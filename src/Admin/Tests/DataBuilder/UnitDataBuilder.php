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

use Admin\Entities\Unit\Unit;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final class UnitDataBuilder
{
    public const UUID_VALID = 'b842c3f4-ec8b-4d39-b1d9-271c2ccd334a';

    private string $uuid;
    private string $label;
    private string $abbreviation;

    public function create(string $label, string $abbreviation): self
    {
        $this->uuid = self::UUID_VALID;
        $this->label = $label;
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function withUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function build(): Unit
    {
        return Unit::create(
            ResourceUuid::fromString($this->uuid),
            NameField::fromString($this->label),
            $this->abbreviation
        );
    }
}
