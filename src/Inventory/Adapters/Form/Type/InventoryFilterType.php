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

use Admin\Contracts\Services\Provider\ZoneStorage\ZoneStorageProvider;
use Inventory\Entities\VO\InventoryStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class InventoryFilterType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ZoneStorageProvider $zoneStorageProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('inventory.status.draft') => InventoryStatus::DRAFT->value,
                    $this->translator->trans('inventory.status.inProgress') => InventoryStatus::IN_PROGRESS->value,
                    $this->translator->trans('inventory.status.review') => InventoryStatus::REVIEW->value,
                    $this->translator->trans('inventory.status.completed') => InventoryStatus::COMPLETED->value,
                    $this->translator->trans('inventory.status.cancelled') => InventoryStatus::CANCELLED->value,
                ],
                'required' => false,
                'placeholder' => $this->translator->trans('inventory.filter.status.all'),
                'label' => $this->translator->trans('inventory.status.label'),
                'data' => $options['status'],
            ])
            ->add('date', FormType::class, [
                'compound' => true,
                'label' => false,
            ])
            ->add('zoneStorage', ChoiceType::class, [
                'choices' => $this->zoneStorageProvider->provideAll()->toSelect(),
                'required' => false,
                'placeholder' => $this->translator->trans('inventory.filter.zoneStorage.all'),
                'label' => $this->translator->trans('inventory.form.zoneStorages.label'),
                'data' => $options['zoneStorage'],
            ])
        ;

        // Sous-formulaire pour date[after] et date[before] (format API Platform)
        $builder->get('date')
            ->add('after', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => $this->translator->trans('inventory.filter.date.after'),
                'data' => $options['dateAfter'],
            ])
            ->add('before', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => $this->translator->trans('inventory.filter.date.before'),
                'data' => $options['dateBefore'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'status' => null,
            'dateAfter' => null,
            'dateBefore' => null,
            'zoneStorage' => null,
        ]);

        $resolver->setAllowedTypes('status', ['null', 'string']);
        $resolver->setAllowedTypes('dateAfter', ['null', \DateTimeImmutable::class]);
        $resolver->setAllowedTypes('dateBefore', ['null', \DateTimeImmutable::class]);
        $resolver->setAllowedTypes('zoneStorage', ['null', 'string']);
    }

    /**
     * Supprime le préfixe du formulaire pour avoir des URLs propres.
     * Ex: ?status=completed au lieu de ?inventory_filter[status]=completed.
     */
    #[\Override]
    public function getBlockPrefix(): string
    {
        return '';
    }
}
