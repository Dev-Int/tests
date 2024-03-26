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

use Admin\Entities\Tax\Tax;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final class TaxDataBuilder
{
    public const UUID_VALID = '288106e1-a5e0-413b-8489-f1b32aa25c25';
    private string $uuid;
    private string $name;
    private float $rate;

    public function create(string $name, float $rate): self
    {
        $this->uuid = self::UUID_VALID;
        $this->name = $name;
        $this->rate = $rate;

        return $this;
    }

    public function build(): Tax
    {
        return Tax::create(
            ResourceUuid::fromString($this->uuid),
            NameField::fromString($this->name),
            $this->rate
        );
    }
}
