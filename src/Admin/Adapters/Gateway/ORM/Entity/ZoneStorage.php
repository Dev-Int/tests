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

use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Entities\ZoneStorage\ZoneStorage as ZoneStorageDomain;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

#[ORM\Entity(repositoryClass: DoctrineZoneStorageRepository::class)]
final class ZoneStorage
{
    #[ORM\Id]
    #[ORM\Column(name: 'uuid', type: 'guid')]
    private string $uuid;
    #[ORM\Column(name: 'label', type: 'string', length: 255)]
    private string $label;
    #[ORM\ManyToOne(targetEntity: FamilyLog::class, inversedBy: 'uuid')]
    #[ORM\JoinColumn(name: 'familyLog_id', referencedColumnName: 'uuid')]
    private FamilyLog $familyLog;
    #[ORM\Column(name: 'slug', type: 'string', length: 255)]
    private string $slug;

    public function fromDomain(ZoneStorageDomain $zoneStorage, FamilyLog $familyLog): self
    {
        $this->uuid = $zoneStorage->uuid()->toString();
        $this->label = $zoneStorage->label()->toString();
        $this->familyLog = $familyLog;
        $this->slug = $zoneStorage->slug();

        return $this;
    }

    public function toDomain(): ZoneStorageDomain
    {
        return ZoneStorageDomain::create(
            ResourceUuid::fromString($this->uuid),
            NameField::fromString($this->label),
            $this->familyLog->toDomain()
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

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    public function setFamilyLog(FamilyLog $familyLog): self
    {
        $this->familyLog = $familyLog;

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
