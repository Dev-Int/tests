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

use Admin\Adapters\Gateway\ORM\Entity\ReadModel\Storage;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class StorageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('unit', EntityType::class, [
                'label' => 'unit',
                'class' => Unit::class,
                'choice_label' => 'label',
                'placeholder' => 'Choice an Unit',
                'empty_data' => null,
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'quantity',
                'html5' => true,
                'input' => 'string',
                'scale' => 3,
                'empty_data' => 0,
            ])
        ;
        $builder->get('quantity')->addModelTransformer(
            new CallbackTransformer(
                static fn (?float $floatAsString): string => $floatAsString !== null ? (string) $floatAsString : '0',
                static fn (?string $stringAsFloat): ?float => $stringAsFloat !== null ? (float) $stringAsFloat : null
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Storage::class,
        ]);
    }
}
