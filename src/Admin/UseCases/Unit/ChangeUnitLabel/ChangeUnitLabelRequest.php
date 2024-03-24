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

namespace Admin\UseCases\Unit\ChangeUnitLabel;

interface ChangeUnitLabelRequest
{
    public function label(): string;

    public function abbreviation(): string;

    public function slug(): string;
}
