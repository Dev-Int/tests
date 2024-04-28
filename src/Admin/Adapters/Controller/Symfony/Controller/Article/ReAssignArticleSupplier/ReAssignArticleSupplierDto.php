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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\ReAssignArticleSupplier;

use Admin\Adapters\Controller\Symfony\Controller\Article\Validator\CompatibleFamilyLogs;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[CompatibleFamilyLogs]
final class ReAssignArticleSupplierDto
{
    /**
     * @param Collection<ZoneStorage> $zoneStorages
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Valid]
        public Supplier $supplier,
        #[Assert\NotBlank]
        #[Assert\Valid]
        public FamilyLog $familyLog,
        #[Assert\NotBlank]
        #[Assert\Valid]
        public Collection $zoneStorages,
        #[Assert\NotBlank]
        #[Assert\Regex('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/')]
        public string $uuid
    ) {
    }
}
