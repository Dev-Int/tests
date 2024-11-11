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
use Symfony\Contracts\Translation\TranslatorInterface;

final class CreateArticleType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('admin.article.form.name.label'),
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('supplier', EntityType::class, [
                'label' => $this->translator->trans('admin.article.form.supplier.label'),
                'class' => Supplier::class,
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => $this->translator->trans('admin.article.form.supplier.placeholder'),
            ])
            ->add('packaging', PackagingType::class, [
                'label' => $this->translator->trans('admin.article.form.packaging.label'),
                'required' => true,
            ])
            ->add('unitPrice', MoneyType::class, [
                'label' => $this->translator->trans('admin.article.form.unitPrice.label'),
                'html5' => false,
                'currency' => 'EUR',
                'divisor' => 100,
                'input' => 'integer',
                'scale' => 2,
                'required' => true,
                'empty_data' => 0.00,
            ])
            ->add('tax', EntityType::class, [
                'label' => $this->translator->trans('admin.article.form.tax.label'),
                'class' => Tax::class,
                'choice_label' => 'rate',
                'required' => true,
                'placeholder' => $this->translator->trans('admin.article.form.tax.placeholder'),
            ])
            ->add('minStock', NumberType::class, [
                'label' => $this->translator->trans('admin.article.form.minStock.label'),
                'html5' => true,
                'scale' => 3,
                'required' => true,
                'empty_data' => 0.0,
            ])
            ->add('zoneStorages', EntityType::class, [
                'label' => $this->translator->trans('admin.article.form.zoneStorages.label'),
                'class' => ZoneStorage::class,
                'choice_label' => 'label',
                'multiple' => true,
                'required' => true,
            ])
            ->add('familyLog', FamilyLogEntitySelectType::class, [
                'label' => $this->translator->trans('admin.article.form.familyLog.label'),
                'required' => true,
                'placeholder' => $this->translator->trans('admin.article.form.familyLog.placeholder'),
            ])
            ->add('quantity', NumberType::class, [
                'label' => $this->translator->trans('admin.article.form.quantity.label'),
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

                        // @codeCoverageIgnoreStart
                        return [
                            'parcel' => $packagingAsArray->parcel,
                            'subPackage' => $packagingAsArray->subPackage,
                            'consumeUnit' => $packagingAsArray->consumeUnit,
                        ];
                        // @codeCoverageIgnoreEnd
                    },
                    static function (?Packaging $packagingAsArray): ?Packaging {
                        if (!$packagingAsArray instanceof Packaging) {
                            // @codeCoverageIgnoreStart
                            return null;
                            // @codeCoverageIgnoreEnd
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
