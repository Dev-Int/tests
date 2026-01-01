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

namespace Admin\UseCases\FamilyLog\ChangeParentFamilyLog;

use Admin\Entities\FamilyLog\FamilyLog;

interface AssignParentFamilyLogRequest
{
    public function uuid(): string;

    public function parent(): ?FamilyLog;
}
