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

use Admin\Adapters\Controller\Symfony\Controller\Article\ChangeArticleFinancialInformation\ChangeArticleFinancialInformationInput;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ChangeFinancialInformationType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('unitPrice', MoneyType::class, [
                'label' => $this->translator->trans('admin.article.form.unitPrice.label'),
                'html5' => false,
                'currency' => 'EUR',
                'divisor' => 100,
                'input' => 'integer',
                'scale' => 2,
                'required' => true,
                'empty_data' => 0,
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('tax', EntityType::class, [
                'label' => $this->translator->trans('admin.article.form.tax.label'),
                'class' => Tax::class,
                'choice_label' => 'rate',
                'required' => true,
                'placeholder' => $this->translator->trans('admin.article.form.tax.placeholder'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangeArticleFinancialInformationInput::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'changeArticleFinancialInformation';
    }
}
