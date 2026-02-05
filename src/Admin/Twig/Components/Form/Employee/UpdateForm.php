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

namespace Admin\Twig\Components\Form\Employee;

use Admin\Adapters\Controller\Symfony\Controller\Employee\UpdateEmployee\UpdateEmployeeInput;
use Admin\Adapters\Form\Type\Employee\UpdateEmployeeType;
use Admin\Adapters\Gateway\ORM\Entity\Employee;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class UpdateForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public UpdateEmployeeInput $initialFormData;

    #[LiveProp]
    public Employee $employee;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UpdateEmployeeType::class, $this->initialFormData);
    }
}
