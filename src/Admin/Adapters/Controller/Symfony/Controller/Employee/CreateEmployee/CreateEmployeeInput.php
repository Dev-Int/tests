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

namespace Admin\Adapters\Controller\Symfony\Controller\Employee\CreateEmployee;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateEmployeeInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
        #[Assert\Length(max: 255)]
        public string $firstName = '',
        #[Assert\NotBlank(message: 'Le nom est obligatoire')]
        #[Assert\Length(max: 255)]
        public string $lastName = '',
        #[Assert\NotBlank(message: 'L\'email est obligatoire')]
        #[Assert\Email(message: 'L\'adresse email n\'est pas valide')]
        public string $email = '',
        #[Assert\NotBlank(message: 'Le téléphone est obligatoire')]
        #[Assert\Length(max: 20)]
        public string $phone = '',
        #[Assert\NotBlank(message: 'Le poste est obligatoire')]
        #[Assert\Length(max: 255)]
        public string $position = '',
        #[Assert\NotBlank(message: 'Le département est obligatoire')]
        #[Assert\Length(max: 255)]
        public string $department = '',
        #[Assert\NotBlank(message: 'La date d\'embauche est obligatoire')]
        public ?\DateTimeImmutable $hiredAt = null,
    ) {
    }
}
