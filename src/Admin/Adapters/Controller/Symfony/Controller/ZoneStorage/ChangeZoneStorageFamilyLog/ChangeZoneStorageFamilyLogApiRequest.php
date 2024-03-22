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

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Entities\FamilyLog as FamilyLogDomain;
use Admin\UseCases\ZoneStorage\ChangeZoneStorageFamilyLog\ChangeZoneStorageFamilyLogRequest;

final class ChangeZoneStorageFamilyLogApiRequest implements ChangeZoneStorageFamilyLogRequest
{
    public function __construct(public FamilyLog $familyLog, public string $slug)
    {
    }

    public function familyLog(): FamilyLogDomain
    {
        return $this->familyLog->toDomain();
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
