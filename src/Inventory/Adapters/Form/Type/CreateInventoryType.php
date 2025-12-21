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
use Inventory\Adapters\Controller\Symfony\Controller\CreateInventory\CreateInventoryInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CreateInventoryType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ZoneStorageProvider $zoneStorageProvider
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'html5' => true,
                'label' => $this->translator->trans('inventory.form.date.label'),
                'required' => true,
                'attr' => [
                    'autofocus' => true,
                    'data-controller' => 'datepicker',
                ],
            ])
            ->add('zoneStorages', ChoiceType::class, [
                'label' => $this->translator->trans('inventory.form.zoneStorages.label'),
                'choices' => $this->zoneStorageProvider->provideAll()->toSelect(),
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateInventoryInput::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'createInventory';
    }
}
