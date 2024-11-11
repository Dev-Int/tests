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

namespace Admin\Adapters\Form\Type\FamilyLog;

use Admin\Adapters\Controller\Symfony\Controller\FamilyLog\CreateFamilyLog\CreateFamilyLogApiRequest;
use Admin\Adapters\Form\Type\Components\FamilyLogEntitySelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateFamilyLogType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'required' => true,
                'label' => $this->translator->trans('admin.familyLog.form.name.label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('admin.familyLog.form.name.placeholder'),
                    'autofocus' => true,
                ],
            ])
            ->add('parent', FamilyLogEntitySelectType::class, [
                'required' => false,
                'label' => $this->translator->trans('admin.familyLog.form.parent.label'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateFamilyLogApiRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'createFamilyLog';
    }
}
