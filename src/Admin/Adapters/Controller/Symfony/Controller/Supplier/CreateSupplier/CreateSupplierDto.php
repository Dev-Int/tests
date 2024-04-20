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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\CreateSupplier;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateSupplierDto
{
    /**
     * @param array<int> $orderDays
     */
    public function __construct(
        #[Assert\NotBlank]
        public string $name = '',
        #[Assert\NotBlank]
        public string $address = '',
        #[Assert\NotBlank]
        public string $postalCode = '',
        #[Assert\NotBlank]
        public string $town = '',
        #[Assert\NotBlank]
        public string $country = '',
        #[Assert\NotBlank]
        public string $phone = '',
        #[Assert\NotBlank]
        public string $email = '',
        #[Assert\NotBlank]
        public string $contact = '',
        #[Assert\NotBlank]
        public string $cellphone = '',
        #[Assert\NotBlank]
        #[Assert\Valid]
        public ?FamilyLog $familyLog = null,
        #[Assert\NotBlank]
        public int $delayDelivery = 1,
        /** @var array<int> */
        #[Assert\NotBlank]
        #[Assert\All([
            new Assert\PositiveOrZero(),
            new Assert\LessThanOrEqual(5),
        ])]
        public array $orderDays = [],
    ) {
    }
}
