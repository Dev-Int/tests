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

namespace Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\GetZoneStorages;

final readonly class ZoneStorageDto
{
    public function __construct(public string $slug, public string $label, public string $familyLogLabel)
    {
    }
}
