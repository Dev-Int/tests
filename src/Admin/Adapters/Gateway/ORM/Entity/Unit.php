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
use Admin\Entities\Unit as UnitDomain;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineUnitRepository::class)]
final class Unit
{
    #[ORM\Id]
    #[ORM\Column(name: 'slug', type: 'string', length: 50)]
    private string $slug;
    #[ORM\Column(name: 'label', type: 'string', length: 50)]
    private string $label;
    #[ORM\Column(name: 'abbreviation', type: 'string', length: 5)]
    private string $abbreviation;

    public function fromDomain(UnitDomain $unit): self
    {
        $this->label = $unit->label()->toString();
        $this->slug = $unit->slug();
        $this->abbreviation = $unit->abbreviation();

        return $this;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function abbreviation(): string
    {
        return $this->abbreviation;
    }
}
