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

namespace Admin\Adapters\Controller\Symfony\Controller\FamilyLog\CreateFamilyLog;

use Admin\Entities\FamilyLog;
use Admin\UseCases\FamilyLog\CreateFamilyLog\CreateFamilyLogRequest;

final readonly class CreateFamilyLogApiRequest implements CreateFamilyLogRequest
{
    public function __construct(public string $label, public ?FamilyLog $parent = null)
    {
    }

    public function label(): string
    {
        return $this->label;
    }

    public function parent(): ?FamilyLog
    {
        return $this->parent;
    }
}
