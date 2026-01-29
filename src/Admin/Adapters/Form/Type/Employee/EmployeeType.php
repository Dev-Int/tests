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

namespace Admin\Adapters\Form\Type\Employee;

use Admin\Adapters\Controller\Symfony\Controller\Employee\CreateEmployee\CreateEmployeeInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmployeeType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => $this->translator->trans('admin.employee.form.firstName.label'),
                'empty_data' => '',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'admin.employee.form.firstName.not_blank'),
                    new Assert\Length(
                        max: 255,
                        maxMessage: 'admin.employee.form.firstName.max_length'
                    ),
                ],
                'attr' => [
                    'autofocus' => true,
                    'placeholder' => $this->translator->trans('admin.employee.form.firstName.placeholder'),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => $this->translator->trans('admin.employee.form.lastName.label'),
                'empty_data' => '',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'admin.employee.form.lastName.not_blank'),
                    new Assert\Length(
                        max: 255,
                        maxMessage: 'admin.employee.form.lastName.max_length'
                    ),
                ],
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.employee.form.lastName.placeholder'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('admin.employee.form.email.label'),
                'empty_data' => '',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'admin.employee.form.email.not_blank'),
                    new Assert\Email(message: 'admin.employee.form.email.invalid'),
                ],
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.employee.form.email.placeholder'),
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => $this->translator->trans('admin.employee.form.phone.label'),
                'empty_data' => '',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'admin.employee.form.phone.not_blank'),
                ],
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.employee.form.phone.placeholder'),
                ],
            ])
            ->add('position', TextType::class, [
                'label' => $this->translator->trans('admin.employee.form.position.label'),
                'empty_data' => '',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'admin.employee.form.position.not_blank'),
                    new Assert\Length(
                        max: 100,
                        maxMessage: 'admin.employee.form.position.max_length'
                    ),
                ],
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.employee.form.position.placeholder'),
                ],
            ])
            ->add('department', TextType::class, [
                'label' => $this->translator->trans('admin.employee.form.department.label'),
                'empty_data' => '',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'admin.employee.form.department.not_blank'),
                    new Assert\Length(
                        max: 100,
                        maxMessage: 'admin.employee.form.department.max_length'
                    ),
                ],
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.employee.form.department.placeholder'),
                ],
            ])
            ->add('hiredAt', DateType::class, [
                'label' => $this->translator->trans('admin.employee.form.hiredAt.label'),
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateEmployeeInput::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'createEmployee';
    }
}
