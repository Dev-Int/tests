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

final class UnitDataBuilder implements DataBuilderInterface
{
    public const UUID_VALID = 'b842c3f4-ec8b-4d39-b1d9-271c2ccd334a';

    // UUID constants for common packaging units
    public const COLIS_UUID = 'a1b2c3d4-1111-4444-8888-000000000001';
    public const PIECE_UUID = 'a1b2c3d4-2222-4444-8888-000000000002';
    public const KILOGRAMME_UUID = 'a1b2c3d4-3333-4444-8888-000000000003';

    // UUID constants for additional standard units
    public const LITRE_UUID = 'a1b2c3d4-4444-4444-8888-000000000004';
    public const BOUTEILLE_UUID = 'a1b2c3d4-5555-4444-8888-000000000005';
    public const BOITE_UUID = 'a1b2c3d4-6666-4444-8888-000000000006';

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
