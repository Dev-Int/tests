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

use Admin\Entities\FamilyLog\FamilyLog;
use Admin\UseCases\FamilyLog\ChangeParentFamilyLog\AssignParentFamilyLogRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class AssignParentFamilyLogApiRequest implements AssignParentFamilyLogRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/')]
        public string $uuid,
        #[Assert\NotBlank]
        #[Assert\Valid]
        public FamilyLog $parent
    ) {
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
