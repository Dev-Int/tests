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

use Admin\Adapters\Controller\Symfony\Controller\Article\ReAssignArticleSupplier\ReAssignArticleSupplierDto;
use Admin\Adapters\Form\Type\Components\FamilyLogEntitySelectType;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ReAssignSupplierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => 'Choice a supplier',
            ])
            ->add('familyLog', FamilyLogEntitySelectType::class, [
                'label' => 'Famille logistique',
                'required' => true,
                'placeholder' => 'Choice a Logistic family',
            ])
            ->add('zoneStorages', EntityType::class, [
                'label' => 'Zone de stockage',
                'class' => ZoneStorage::class,
                'choice_label' => 'label',
                'multiple' => true,
                'required' => true,
            ])
            ->add('uuid', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReAssignArticleSupplierDto::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'reAssignArticleSupplier';
    }
}
