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

use Admin\Adapters\Controller\Symfony\Controller\Supplier\ChangeDeliverySpecificationSupplier\ChangeDeliverySpecificationSupplierDto;
use Admin\Adapters\Form\Type\Components\FamilyLogEntitySelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChangeDeliverySpecificationSupplierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('familyLog', FamilyLogEntitySelectType::class, [
                'label' => 'Famille logistique',
                'required' => true,
                'attr' => [
                    'autofocus' => true,
                ],
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
            ->add('slug', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangeDeliverySpecificationSupplierDto::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'changeDeliverySpecificationSupplier';
    }
}
