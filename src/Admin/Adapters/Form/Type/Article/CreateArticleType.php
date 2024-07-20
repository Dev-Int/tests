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

use Admin\Adapters\Controller\Symfony\Controller\Article\CreateArticle\CreateArticleInput;
use Admin\Adapters\Form\Type\Components\FamilyLogEntitySelectType;
use Admin\Adapters\Gateway\ORM\Entity\ReadModel\Packaging;
use Admin\Adapters\Gateway\ORM\Entity\ReadModel\Storage;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'article',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => 'Choice a supplier',
            ])
            ->add('packaging', PackagingType::class, [
                'label' => 'Packaging de l\'article',
                'required' => true,
            ])
            ->add('amount', MoneyType::class, [
                'html5' => false,
                'currency' => 'EUR',
                'divisor' => 100,
                'input' => 'integer',
                'scale' => 2,
                'required' => true,
                'empty_data' => 0.00,
            ])
            ->add('tax', EntityType::class, [
                'label' => 'TVA de l\'article',
                'class' => Tax::class,
                'choice_label' => 'rate',
                'required' => true,
                'placeholder' => 'Choice a tax',
            ])
            ->add('minStock', NumberType::class, [
                'label' => 'Stock minimum',
                'html5' => true,
                'scale' => 3,
                'required' => true,
                'empty_data' => 0.0,
            ])
            ->add('zoneStorages', EntityType::class, [
                'label' => 'Zone de stockage',
                'class' => ZoneStorage::class,
                'choice_label' => 'label',
                'multiple' => true,
                'required' => true,
            ])
            ->add('familyLog', FamilyLogEntitySelectType::class, [
                'label' => 'Famille logistique',
                'required' => true,
                'placeholder' => 'Choice a Logistic family',
            ])
            ->add('quantity', NumberType::class, [
                'html5' => true,
                'required' => false,
                'scale' => 3,
            ])
            ->get('packaging')->addModelTransformer(
                new CallbackTransformer(
                    static function (?Packaging $packagingAsArray): ?array {
                        if (!$packagingAsArray instanceof Packaging) {
                            return null;
                        }

                        return [
                            'parcel' => $packagingAsArray->parcel,
                            'subPackage' => $packagingAsArray->subPackage,
                            'consumeUnit' => $packagingAsArray->consumeUnit,
                        ];
                    },
                    static function (?Packaging $packagingAsArray): ?Packaging {
                        if (!$packagingAsArray instanceof Packaging) {
                            return null;
                        }

                        return new Packaging(
                            parcel: new Storage(
                                $packagingAsArray->parcel?->unit,
                                $packagingAsArray->parcel?->quantity
                            ),
                            subPackage: new Storage(
                                $packagingAsArray->subPackage?->unit,
                                $packagingAsArray->subPackage?->quantity
                            ),
                            consumeUnit: new Storage(
                                $packagingAsArray->consumeUnit?->unit,
                                $packagingAsArray->consumeUnit?->quantity
                            )
                        );
                    }
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateArticleInput::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'createArticle';
    }
}
