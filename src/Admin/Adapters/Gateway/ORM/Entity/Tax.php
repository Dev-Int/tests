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

use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Entities\Tax\Tax as TaxDomain;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

#[ORM\Entity(repositoryClass: DoctrineTaxRepository::class)]
final class Tax
{
    #[ORM\Id]
    #[ORM\Column(name: 'uuid', type: 'guid')]
    private string $uuid;
    #[ORM\Column(name: 'name', type: 'string', length: 100)]
    private string $name;
    #[ORM\Column(name: 'rate', type: 'float', precision: 3, scale: 2)]
    private float $rate;

    public function fromDomain(TaxDomain $tax): self
    {
        $this->uuid = $tax->uuid()->toString();
        $this->name = $tax->name()->toString();
        $this->rate = $tax->rate();

        return $this;
    }

    public function toDomain(): TaxDomain
    {
        return TaxDomain::create(
            ResourceUuid::fromString($this->uuid),
            NameField::fromString($this->name),
            $this->rate
        );
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function rate(): float
    {
        return $this->rate;
    }
}
