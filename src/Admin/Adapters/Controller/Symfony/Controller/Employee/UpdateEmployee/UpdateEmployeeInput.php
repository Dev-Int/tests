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

namespace Admin\Adapters\Controller\Symfony\Controller\Employee\UpdateEmployee;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateEmployeeInput
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public \DateTimeImmutable $hiredAt,
        #[Assert\NotBlank]
        public string $phone = '',
        #[Assert\NotBlank]
        public string $position = '',
        #[Assert\NotBlank]
        public string $department = '',
    ) {
    }
}
