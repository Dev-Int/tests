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

namespace Admin\Adapters\Controller\Symfony\Controller\Tax\CreateTax;

use Admin\UseCases\Tax\CreateTax\CreateTaxRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateTaxApiRequest implements CreateTaxRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name = '',
        #[Assert\NotBlank]
        public float $rate = 0.0
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function rate(): float
    {
        return $this->rate;
    }
}
