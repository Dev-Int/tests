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

namespace Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\ChangeZoneStorageFamilyLog;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangeZoneStorageFamilyLogDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Valid]
        public FamilyLog $familyLog,
        #[Assert\NotBlank]
        public string $slug
    ) {
    }
}
