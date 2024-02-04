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

namespace Admin\UseCases\Gateway;

use Admin\Entities\FamilyLog;

interface FamilyLogRepository
{
    public function save(FamilyLog $familyLog): void;

    public function exists(string $name, ?FamilyLog $parent): bool;
}
