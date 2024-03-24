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

namespace Admin\Adapters\Gateway\ORM\Entity;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Entities\Unit\Unit as UnitDomain;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

#[ORM\Entity(repositoryClass: DoctrineUnitRepository::class)]
final class Unit
{
    #[ORM\Id]
    #[ORM\Column(name: 'uuid', type: 'guid')]
    private string $uuid;
    #[ORM\Column(name: 'slug', type: 'string', length: 50)]
    private string $slug;
    #[ORM\Column(name: 'label', type: 'string', length: 50)]
    private string $label;
    #[ORM\Column(name: 'abbreviation', type: 'string', length: 5)]
    private string $abbreviation;

    public function fromDomain(UnitDomain $unit): self
    {
        $this->uuid = $unit->uuid()->toString();
        $this->label = $unit->label()->toString();
        $this->slug = $unit->slug();
        $this->abbreviation = $unit->abbreviation();

        return $this;
    }

    public function toDomain(): UnitDomain
    {
        return UnitDomain::create(
            ResourceUuid::fromString($this->uuid),
            NameField::fromString($this->label),
            $this->abbreviation
        );
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function abbreviation(): string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
