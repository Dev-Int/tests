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

namespace Auth\Adapters\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class PasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options' => ['label' => 'auth.reset_password.form.password.label'],
            'second_options' => ['label' => 'auth.reset_password.form.password.confirm_password'],
            'mapped' => false,
            'constraints' => [
                new Assert\NotBlank(message: 'auth.reset_password.form.password.not_blank'),
                new Assert\Length(
                    min: 8,
                    minMessage: 'auth.reset_password.form.password.min_length'
                ),
                new Assert\NotCompromisedPassword(message: 'auth.reset_password.form.password.compromised'),
            ],
        ]);
    }
}
