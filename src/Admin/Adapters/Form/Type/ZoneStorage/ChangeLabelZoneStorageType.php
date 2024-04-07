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

use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\ChangeZoneStorageLabel\ChangeZoneStorageLabelApiRequest;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChangeLabelZoneStorageType extends ZoneStorageType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('familyLog')
            ->add('slug', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangeZoneStorageLabelApiRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'changeZoneStorageLabel';
    }
}
