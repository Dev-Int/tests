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

namespace Admin\Adapters\Controller\Symfony\Controller\Tax\Revaluate;

use Admin\UseCases\Tax\RevaluateTax\RevaluateTaxRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class RevaluateTaxApiRequest implements RevaluateTaxRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public float $rate,
        #[Assert\Regex('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/')]
        #[Assert\NotBlank]
        public string $uuid
    ) {
    }

    public function rate(): float
    {
        return $this->rate;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}
