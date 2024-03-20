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

namespace Admin\Tests\DataBuilder;

use Admin\Entities\FamilyLog;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Shared\Entities\VO\NameField;

final class ZoneStorageDataBuilder
{
    private string $label;

    private FamilyLog $familyLog;

    public function create(string $label, FamilyLog $familyLog): self
    {
        $this->label = $label;
        $this->familyLog = $familyLog;

        return $this;
    }

    public function build(): ZoneStorage
    {
        return ZoneStorage::create(NameField::fromString($this->label), $this->familyLog);
    }
}
