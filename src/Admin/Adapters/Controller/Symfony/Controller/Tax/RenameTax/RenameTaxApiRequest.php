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

namespace Admin\Adapters\Controller\Symfony\Controller\Tax\RenameTax;

use Admin\UseCases\Tax\RenameTax\RenameTaxRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class RenameTaxApiRequest implements RenameTaxRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,
        #[Assert\Regex('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/')]
        #[Assert\NotBlank]
        public string $uuid,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}
