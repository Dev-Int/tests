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

namespace Admin\Adapters\Form\Type\Components;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FamilyLogEntitySelectType extends EntityType
{
    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => FamilyLog::class,
            'query_builder' => static function (EntityRepository $repository): QueryBuilder {
                return $repository->createQueryBuilder('f')->orderBy('f.slug', 'asc');
            },
            'choice_label' => 'levelChildrenLabel',
            'expanded' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'familyLog';
    }
}
