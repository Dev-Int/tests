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

namespace Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\ChangeZoneStorageLabel;

use Admin\UseCases\ZoneStorage\ChangeZoneStorageLabel\ChangeZoneStorageLabelRequest;

final readonly class ChangeZoneStorageLabelApiRequest implements ChangeZoneStorageLabelRequest
{
    public function __construct(public string $label, public string $slug)
    {
    }

    public function label(): string
    {
        return $this->label;
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
