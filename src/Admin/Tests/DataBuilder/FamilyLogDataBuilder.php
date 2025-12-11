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

use Admin\Entities\FamilyLog\FamilyLog;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final class FamilyLogDataBuilder implements DataBuilderInterface
{
    public const VALID_UUID = '48e50c4f-7d87-427c-bc93-c67564664266';

    // UUID constants for standard FamilyLog hierarchy
    public const ALIMENTAIRE_UUID = 'f1a2m3i4-0000-4444-8888-000000000001';
    public const SURGELE_UUID = 'f1a2m3i4-0000-4444-8888-000000000002';
    public const SURGELE_VIANDE_UUID = 'f1a2m3i4-0000-4444-8888-000000000003';
    public const SURGELE_FRUITS_LEGUMES_UUID = 'f1a2m3i4-0000-4444-8888-000000000004';
    public const FRAIS_UUID = 'f1a2m3i4-0000-4444-8888-000000000005';
    public const FRAIS_VIANDE_UUID = 'f1a2m3i4-0000-4444-8888-000000000006';
    public const FRAIS_FRUITS_LEGUMES_UUID = 'f1a2m3i4-0000-4444-8888-000000000007';
    public const EPICERIE_UUID = 'f1a2m3i4-0000-4444-8888-000000000008';
    public const NON_ALIMENTAIRE_UUID = 'f1a2m3i4-0000-4444-8888-000000000009';
    public const EMBALLAGE_UUID = 'f1a2m3i4-0000-4444-8888-000000000010';
    public const HYGIENE_UUID = 'f1a2m3i4-0000-4444-8888-000000000011';

    private string $uuid;
    private string $label;
    private ?FamilyLog $parent = null;

    public function create(string $label): self
    {
        $this->uuid = self::VALID_UUID;
        $this->label = $label;
        $this->parent = null;

        return $this;
    }

    public function withUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function withParent(?FamilyLog $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function build(): FamilyLog
    {
        return FamilyLog::create(
            ResourceUuid::fromString($this->uuid),
            NameField::fromString($this->label),
            $this->parent
        );
    }
}
