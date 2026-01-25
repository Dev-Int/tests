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

use Admin\Adapters\Controller\Symfony\Controller\Employee\UpdateEmployee\UpdateEmployeeInput;
use Admin\Entities\VO\EmployeeStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UpdateEmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'admin.employee.form.firstName.label',
                'disabled' => true,
                'required' => false,
                'help' => 'admin.employee.update.immutableField',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'admin.employee.form.lastName.label',
                'disabled' => true,
                'required' => false,
                'help' => 'admin.employee.update.immutableField',
            ])
            ->add('hiredAt', DateType::class, [
                'label' => 'admin.employee.form.hiredAt.label',
                'disabled' => true,
                'required' => false,
                'help' => 'admin.employee.update.immutableField',
            ])
            ->add('email', EmailType::class, [
                'label' => 'admin.employee.form.email.label',
            ])
            ->add('phone', TextType::class, [
                'label' => 'admin.employee.form.phone.label',
            ])
            ->add('position', TextType::class, [
                'label' => 'admin.employee.form.position.label',
            ])
            ->add('department', TextType::class, [
                'label' => 'admin.employee.form.department.label',
            ])
            ->add('status', EnumType::class, [
                'class' => EmployeeStatus::class,
                'label' => 'admin.employee.form.status.label',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateEmployeeInput::class,
        ]);
    }
}
