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
            ])
        ;

        // Sous-formulaire pour date[after] et date[before] (format API Platform)
        $builder->get('date')
            ->add('after', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
                'label' => $this->translator->trans('inventory.filter.date.after'),
                'invalid_message' => $this->translator->trans('inventory.filter.date.invalid'),
            ])
            ->add('before', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
                'label' => $this->translator->trans('inventory.filter.date.before'),
                'invalid_message' => $this->translator->trans('inventory.filter.date.invalid'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            // Autorise les paramètres query supplémentaires (page, itemsPerPage)
            // car getBlockPrefix() vide expose tous les params GET au formulaire
            'allow_extra_fields' => true,
        ]);
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
