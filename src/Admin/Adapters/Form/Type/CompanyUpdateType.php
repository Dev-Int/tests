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

namespace Admin\Adapters\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

final class CompanyUpdateType extends CompanyType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->remove('name');
    }

    public function getBlockPrefix(): string
    {
        return 'updateCompany';
    }
}
