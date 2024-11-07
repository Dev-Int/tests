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

namespace Admin\Adapters\Form\Type\Company;

use Admin\Adapters\Controller\Symfony\Controller\Company\CreateCompany\CreateCompanyApiRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => $this->translator->trans('admin.company.form.name.label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.company.form.name.placeholder'),
                    'autofocus' => true,
                ],
            ])
            ->add('address', TextType::class, [
                'required' => true,
                'label' => $this->translator->trans('admin.company.form.address.label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.company.form.address.placeholder'),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'required' => true,
                'label' => $this->translator->trans('admin.company.form.postalCode.label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.company.form.postalCode.placeholder'),
                ],
            ])
            ->add('city', TextType::class, [
                'required' => true,
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.company.form.city.placeholder'),
                ],
            ])
            ->add('country', TextType::class, [
                'required' => true,
                'label' => $this->translator->trans('admin.company.form.country.label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.company.form.country.placeholder'),
                ],
            ])
            ->add('phone', TelType::class, [
                'required' => true,
                'label' => $this->translator->trans('admin.company.form.phone.label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.company.form.phone.placeholder'),
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => $this->translator->trans('admin.company.form.email.label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.company.form.email.placeholder'),
                ],
            ])
            ->add('contact', TextType::class, [
                'required' => true,
                'label' => $this->translator->trans('admin.company.form.contact.label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.company.form.contact.placeholder'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateCompanyApiRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'createCompany';
    }
}
