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

namespace Inventory\Adapters\Form\Type;

use Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\Input\ItemChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ReviewInventoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<ItemChoice> $items */
        $items = $options['items'];

        $choices = array_filter(
            $items,
            static fn (ItemChoice $item): bool => !$item->isReviewed
        );

        $builder->add('reviewed_items', ChoiceType::class, [
            'choices' => $choices,
            'choice_value' => static fn (ItemChoice $item): string => $item->identifier,
            'choice_label' => static fn (ItemChoice $item): string => $item->articleName,
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'items' => [],
        ]);
        $resolver->setAllowedTypes('items', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'review_discrepancies';
    }
}
