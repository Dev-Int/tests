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

namespace Admin\Adapters\Controller\Symfony\Controller\Unit\ChangeUnitLabel;

use Admin\UseCases\Unit\ChangeUnitLabel\ChangeUnitLabelRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangeUnitLabelApiRequest implements ChangeUnitLabelRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 50)]
        public string $label,
        #[Assert\NotBlank]
        #[Assert\Length(max: 5)]
        public string $abbreviation,
        public readonly string $slug
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

    public function slug(): string
    {
        return $this->slug;
    }
}
