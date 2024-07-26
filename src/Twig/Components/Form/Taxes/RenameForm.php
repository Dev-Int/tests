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

namespace App\Twig\Components\Form\Taxes;

use Admin\Adapters\Controller\Symfony\Controller\Tax\RenameTax\RenameTaxApiRequest;
use Admin\Adapters\Form\Type\Tax\RenameTaxType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class RenameForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public RenameTaxApiRequest $initialFormData;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(RenameTaxType::class, $this->initialFormData);
    }
}
