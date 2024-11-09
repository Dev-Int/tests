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

namespace Admin\Adapters\Form\Type\Tax;

use Admin\Adapters\Controller\Symfony\Controller\Tax\CreateTax\CreateTaxApiRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaxType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('admin.tax.form.name.label'),
                'empty_data' => '',
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('rate', PercentType::class, [
                'label' => $this->translator->trans('admin.tax.form.rate.label'),
                'empty_data' => 0.0,
                'html5' => false,
                'scale' => 2,
                'attr' => [
                    'autofocus' => true,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateTaxApiRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'createTax';
    }
}
