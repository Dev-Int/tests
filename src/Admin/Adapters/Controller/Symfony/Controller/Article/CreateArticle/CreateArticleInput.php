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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\CreateArticle;

use Admin\Adapters\Controller\Symfony\Controller\Article\Validator\CompatibleFamilyLogs;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[CompatibleFamilyLogs]
final class CreateArticleInput
{
    /**
     * @param array{
     *     parcel: array{unit: Unit, quantity: string},
     *     subPackage: array{unit: Unit|null, quantity: string|null},
     *     consumeUnit: array{unit: Unit|null, quantity: string|null}
     *     } $packaging
     * @param Collection<ZoneStorage> $zoneStorages
     */
    public function __construct(
        #[Assert\NotBlank]
        public string $name = '',
        #[Assert\NotBlank]
        #[Assert\Valid]
        public ?Supplier $supplier = null,
        #[Assert\NotBlank]
        public ?array $packaging = null,
        #[Assert\NotBlank]
        public int $amount = 0,
        #[Assert\NotBlank]
        #[Assert\Valid]
        public ?Tax $tax = null,
        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public float $minStock = 0.0,
        #[Assert\NotBlank]
        #[Assert\Valid]
        public Collection $zoneStorages = new ArrayCollection(),
        #[Assert\NotBlank]
        #[Assert\Valid]
        public ?FamilyLog $familyLog = null,
        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public ?float $quantity = 0.0
    ) {
    }
}
