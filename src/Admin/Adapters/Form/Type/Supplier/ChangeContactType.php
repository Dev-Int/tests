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

use Admin\Adapters\Controller\Symfony\Controller\Supplier\ChangeContactSupplier\ChangeContactSupplierApiRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChangeContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('slug', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangeContactSupplierApiRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'changeContactSupplier';
    }
}
