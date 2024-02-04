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
use Shared\Entities\VO\NameField;

final class FamilyLogDataBuilder
{
    private NameField $name;
    private ?FamilyLog $parent = null;

    public function create(string $name): self
    {
        $this->name = NameField::fromString($name);

        return $this;
    }

    public function withParent(string $parentName): self
    {
        $this->parent = FamilyLog::create(NameField::fromString($parentName));

        return $this;
    }

    public function build(): FamilyLog
    {
        return FamilyLog::create($this->name, $this->parent);
    }
}
