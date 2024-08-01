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

use Admin\Adapters\Controller\Symfony\Controller\Article\ChangeArticleStorageInformation\ChangeArticleStorageInformationInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChangeStorageInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('packaging', PackagingType::class, [
                'label' => 'Packaging de l\'article',
                'required' => true,
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('minStock', NumberType::class, [
                'label' => 'Stock minimum',
                'html5' => true,
                'scale' => 3,
                'required' => true,
                'empty_data' => 0.0,
            ])->add('uuid', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangeArticleStorageInformationInput::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'changeArticleStorageInformation';
    }
}
