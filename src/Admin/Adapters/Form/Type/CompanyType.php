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

namespace Admin\Adapters\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom de la société',
                'attr' => [
                    'placeholder' => 'Le nom de votre société',
                ],
            ])
            ->add('address', TextType::class, [
                'required' => true,
                'label' => 'Adresse de la société',
                'attr' => [
                    'placeholder' => 'L\'adresse de votre société',
                ],
            ])
            ->add('postalCode', TextType::class, [
                'required' => true,
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => 'Le code Postal où est domiciliée votre société',
                ],
            ])
            ->add('town', TextType::class, [
                'required' => true,
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'La ville où est domiciliée votre société',
                ],
            ])
            ->add('country', TextType::class, [
                'required' => true,
                'label' => 'Pays',
                'attr' => [
                    'placeholder' => 'Le pays où est domiciliée votre société',
                ],
            ])
            ->add('phone', TextType::class, [
                'required' => true,
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => 'Le téléphone de votre société',
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Adresse mail',
                'attr' => [
                    'placeholder' => 'L\'adresse mail de votre société',
                ],
            ])
            ->add('contact', TextType::class, [
                'required' => true,
                'label' => 'Contact de la société',
                'attr' => [
                    'placeholder' => 'Le nom du contact de votre société',
                ],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'createCompany';
    }
}
