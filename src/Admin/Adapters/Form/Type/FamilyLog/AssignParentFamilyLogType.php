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

namespace Admin\Adapters\Form\Type\FamilyLog;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

final class AssignParentFamilyLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parent', EntityType::class, [
                'class' => FamilyLog::class,
                'query_builder' => static function (EntityRepository $repository): QueryBuilder {
                    return $repository->createQueryBuilder('f')
                        ->orderBy('f.slug', 'asc')
                    ;
                },
                'choice_label' => 'indentedLabel',
                'required' => false,
                'label' => 'Famille logistique parente',
                'attr' => [
                    'placeholder' => 'La famille logistique parente',
                ],
            ])
            ->add('uuid', HiddenType::class)
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'assignParentFamilyLog';
    }
}
