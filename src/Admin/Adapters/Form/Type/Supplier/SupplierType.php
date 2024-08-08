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

namespace Admin\Adapters\Form\Type\Supplier;

use Admin\Adapters\Controller\Symfony\Controller\Supplier\CreateSupplier\CreateSupplierDto;
use Admin\Adapters\Form\Type\Components\FamilyLogEntitySelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SupplierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse de l\'entreprise',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('town', TextType::class, [
                'label' => 'Ville',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone de l\'entreprise',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('contact', TextType::class, [
                'label' => 'Nom du contact',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('cellphone', TextType::class, [
                'label' => 'Téléphone du contact',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('familyLog', FamilyLogEntitySelectType::class, [
                'label' => 'Famille logistique',
                'required' => true,
            ])
            ->add('delayDelivery', NumberType::class, [
                'label' => 'Délai de livraison',
                'required' => true,
                'html5' => true,
                'empty_data' => 1,
            ])
            ->add('orderDays', ChoiceType::class, [
                'label' => 'Jour(s) de commande',
                'required' => true,
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'lundi' => 0,
                    'mardi' => 1,
                    'mercredi' => 2,
                    'jeudi' => 3,
                    'vendredi' => 4,
                    'samedi' => 5,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateSupplierDto::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'createSupplier';
    }
}
