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

namespace Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\CreateZoneStorage;

use Admin\Entities\FamilyLog;
use Admin\UseCases\ZoneStorage\CreateZoneStorage\CreateZoneStorageRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateZoneStorageApiRequest implements CreateZoneStorageRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $label,
        #[Assert\NotBlank]
        #[Assert\Valid]
        public FamilyLog $familyLog
    ) {
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
