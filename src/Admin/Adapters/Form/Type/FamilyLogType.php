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

namespace Admin\Adapters\Form\Type;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class FamilyLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'required' => true,
                'label' => 'Nom de la famille logistique',
                'attr' => [
                    'placeholder' => 'Le nom de la famille logistique',
                ],
            ])
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
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'familyLog';
    }
}
