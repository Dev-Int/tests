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

use Admin\Adapters\Gateway\ORM\Entity\ReadModel\Packaging;
use Admin\Adapters\Gateway\ORM\Entity\ReadModel\Storage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $builder->get('parcel')->addModelTransformer(
            new CallbackTransformer(
                static function (?Storage $storage): ?Storage {
                    if (!$storage instanceof Storage) {
                        return null;
                    }

                    return $storage;
                },
                static function (?Storage $storage): ?Storage {
                    if (!$storage instanceof Storage) {
                        return null;
                    }

                    return $storage;
                }
            )
        );
        $builder->get('subPackage')->addModelTransformer(
            new CallbackTransformer(
                static function (?Storage $storage): ?Storage {
                    if (!$storage instanceof Storage) {
                        return null;
                    }

                    return $storage;
                },
                static function (?Storage $storage): ?Storage {
                    if (!$storage instanceof Storage) {
                        return null;
                    }

                    return $storage;
                }
            )
        );
        $builder->get('consumeUnit')->addModelTransformer(
            new CallbackTransformer(
                static function (?Storage $storage): ?Storage {
                    if (!$storage instanceof Storage) {
                        return null;
                    }

                    return $storage;
                },
                static function (?Storage $storage): ?Storage {
                    if (!$storage instanceof Storage) {
                        return null;
                    }

                    return $storage;
                }
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Packaging::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'packaging';
    }
}
