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

final class TaxDataBuilder implements DataBuilderInterface
{
    public const UUID_VALID = '288106e1-a5e0-413b-8489-f1b32aa25c25';

    // UUID constants for standard French VAT rates
    public const TAUX_NORMAL_UUID = 't1a2x3n4-0000-4444-8888-000000000001';
    public const TAUX_INTERMEDIAIRE_UUID = 't1a2x3n4-0000-4444-8888-000000000002';
    public const TAUX_REDUIT_UUID = 't1a2x3n4-0000-4444-8888-000000000003';
    public const TAUX_PARTICULIER_UUID = 't1a2x3n4-0000-4444-8888-000000000004';

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

    public function withUuid(string $uuid): self
    {
        $this->uuid = $uuid;

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
