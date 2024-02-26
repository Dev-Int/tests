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

namespace Admin\Adapters\Controller\Symfony\Controller\FamilyLog\AssignParentFamilyLog;

use Admin\Entities\FamilyLog;
use Admin\UseCases\FamilyLog\ChangeParentFamilyLog\AssignParentFamilyLogRequest;

final class AssignParentFamilyLogApiRequest implements AssignParentFamilyLogRequest
{
    public function __construct(public string $uuid, public FamilyLog $parent)
    {
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function parent(): FamilyLog
    {
        return $this->parent;
    }
}
