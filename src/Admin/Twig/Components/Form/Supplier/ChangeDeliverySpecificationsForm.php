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

namespace Admin\Twig\Components\Form\Supplier;

use Admin\Adapters\Controller\Symfony\Controller\Supplier\ChangeDeliverySpecificationsSupplier\ChangeDeliverySpecificationsSupplierDto;
use Admin\Adapters\Form\Type\Supplier\ChangeDeliverySpecificationsSupplierType;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ChangeDeliverySpecificationsForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ChangeDeliverySpecificationsSupplierDto $initialFormData;

    #[LiveProp]
    public Supplier $supplier;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ChangeDeliverySpecificationsSupplierType::class, $this->initialFormData);
    }
}
