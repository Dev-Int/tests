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

namespace Admin\Adapters\Form\Type\Article;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class PackagingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parcel', StorageType::class, [
                'label' => 'Colis',
                'required' => true,
            ])
            ->add('subPackage', StorageType::class, [
                'label' => 'Sous-colis',
                'required' => false,
            ])
            ->add('consumeUnit', StorageType::class, [
                'label' => 'Unité de consommation',
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'packaging';
    }
}
