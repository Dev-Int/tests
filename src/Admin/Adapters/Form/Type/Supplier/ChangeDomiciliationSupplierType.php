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

use Admin\Adapters\Controller\Symfony\Controller\Supplier\ChangeDomiciliationSupplier\ChangeDomiciliationSupplierApiRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChangeDomiciliationSupplierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class, [
                'label' => 'Adresse de l\'entreprise',
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'autofocus' => true,
                ],
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
            ->add('slug', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangeDomiciliationSupplierApiRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'changeDomiciliationSupplier';
    }
}
