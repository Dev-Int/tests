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

namespace Admin\Adapters\Controller\Symfony\Controller\FamilyLog\ChangeLabelFamilyLog;

use Admin\UseCases\FamilyLog\ChangeLabelFamilyLog\ChangeLabelFamilyLogRequest;

final readonly class ChangeLabelFamilyLogApiRequest implements ChangeLabelFamilyLogRequest
{
    public function __construct(public string $slug, public string $label)
    {
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function label(): string
    {
        return $this->label;
    }
}
