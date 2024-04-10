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
use Symfony\Component\Validator\Constraints as Assert;

final class ChangeLabelFamilyLogApiRequest implements ChangeLabelFamilyLogRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/')]
        public string $uuid,
        #[Assert\NotBlank]
        public string $label
    ) {
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function label(): string
    {
        return $this->label;
    }
}
