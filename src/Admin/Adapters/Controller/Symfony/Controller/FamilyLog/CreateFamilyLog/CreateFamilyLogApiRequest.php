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

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Entities\FamilyLog as FamilyLogDomain;
use Admin\UseCases\FamilyLog\CreateFamilyLog\CreateFamilyLogRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateFamilyLogApiRequest implements CreateFamilyLogRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $label = '',
        #[Assert\Valid]
        public ?FamilyLog $parent = null
    ) {
    }

    public function label(): string
    {
        return $this->label;
    }

    public function parent(): ?FamilyLogDomain
    {
        return $this->parent?->toDomain();
    }
}
