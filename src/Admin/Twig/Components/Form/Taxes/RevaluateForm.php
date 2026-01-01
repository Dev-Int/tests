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

namespace Admin\Twig\Components\Form\Taxes;

use Admin\Adapters\Controller\Symfony\Controller\Tax\Revaluate\RevaluateTaxApiRequest;
use Admin\Adapters\Form\Type\Tax\RevaluateTaxType;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class RevaluateForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public Tax $tax;

    #[LiveProp]
    public RevaluateTaxApiRequest $initialFormData;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(RevaluateTaxType::class, $this->initialFormData);
    }
}
