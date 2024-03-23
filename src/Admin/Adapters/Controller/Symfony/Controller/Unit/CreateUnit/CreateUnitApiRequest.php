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

namespace Admin\Adapters\Controller\Symfony\Controller\Unit\CreateUnit;

use Admin\UseCases\Unit\CreateUnit\CreateUnitRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateUnitApiRequest implements CreateUnitRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 50)]
        public string $label = '',
        #[Assert\NotBlank]
        #[Assert\Length(max: 5)]
        public string $abbreviation = ''
    ) {
    }

    public function label(): string
    {
        return $this->label;
    }

    public function abbreviation(): string
    {
        return $this->abbreviation;
    }
}
