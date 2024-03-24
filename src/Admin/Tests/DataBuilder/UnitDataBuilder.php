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

use Admin\Entities\Unit\Unit;
use Shared\Entities\VO\NameField;

final class UnitDataBuilder
{
    private string $label;
    private string $abbreviation;

    public function create(string $label, string $abbreviation): self
    {
        $this->label = $label;
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function build(): Unit
    {
        return Unit::create(NameField::fromString($this->label), $this->abbreviation);
    }
}
