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

use Admin\Adapters\Controller\Symfony\Controller\Unit\CreateUnit\CreateUnitApiRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UnitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Intitulé de l\'unité',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('abbreviation', TextType::class, [
                'label' => 'Abréviation de l\'unité',
                'required' => true,
                'empty_data' => '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateUnitApiRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'createUnit';
    }
}
