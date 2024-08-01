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

namespace App\Twig\Components\Form\Supplier;

use Admin\Adapters\Controller\Symfony\Controller\Supplier\ChangeContactSupplier\ChangeContactSupplierApiRequest;
use Admin\Adapters\Form\Type\Supplier\ChangeContactType;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ChangeContactForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ChangeContactSupplierApiRequest $initialFormData;

    #[LiveProp]
    public Supplier $supplier;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ChangeContactType::class, $this->initialFormData);
    }
}
