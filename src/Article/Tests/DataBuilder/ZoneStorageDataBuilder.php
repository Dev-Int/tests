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

namespace Article\Tests\DataBuilder;

use Article\Entities\Component\ZoneStorage;
use Shared\Entities\VO\NameField;

final class ZoneStorageDataBuilder
{
    private NameField $name;

    public function __construct(string $name)
    {
        $this->name = NameField::fromString($name);
    }

    public function build(): ZoneStorage
    {
        return ZoneStorage::create($this->name);
    }
}
