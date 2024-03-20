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
use Admin\Entities\ZoneStorage as ZoneStorageDomain;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\VO\NameField;

#[ORM\Entity(repositoryClass: DoctrineZoneStorageRepository::class)]
final class ZoneStorage
{
    #[ORM\Id]
    #[ORM\Column(name: 'slug', type: 'string', length: 255)]
    private string $slug;
    #[ORM\Column(name: 'label', type: 'string', length: 255)]
    private string $label;
    #[ORM\ManyToOne(targetEntity: FamilyLog::class, inversedBy: 'uuid')]
    #[ORM\JoinColumn(name: 'familyLog_id', referencedColumnName: 'uuid')]
    private FamilyLog $familyLog;

    public function fromDomain(ZoneStorageDomain $zoneStorage, FamilyLog $familyLog): self
    {
        $this->slug = $zoneStorage->slug();
        $this->label = $zoneStorage->label()->toString();
        $this->familyLog = $familyLog;

        return $this;
    }

    public function toDomain(): ZoneStorageDomain
    {
        return ZoneStorageDomain::create(NameField::fromString($this->label), $this->familyLog->toDomain());
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }
}
