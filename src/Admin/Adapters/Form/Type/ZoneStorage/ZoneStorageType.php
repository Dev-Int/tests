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

namespace Admin\Adapters\Form\Type\ZoneStorage;

use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\CreateZoneStorage\CreateZoneStorageDto;
use Admin\Adapters\Form\Type\Components\FamilyLogEntitySelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @group functionalTest
 */
class ZoneStorageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'required' => true,
                'label' => 'Nom de la zone de stockage',
                'attr' => [
                    'placeholder' => 'Le nom de la zone de stockage',
                    'autofocus' => true,
                ],
            ])
            ->add('familyLog', FamilyLogEntitySelectType::class, [
                'required' => true,
                'label' => 'Famille logistique',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateZoneStorageDto::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'createZoneStorage';
    }
}
