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

namespace App\Twig\Components\Form\FamilyLog;

use Admin\Adapters\Controller\Symfony\Controller\FamilyLog\CreateFamilyLog\CreateFamilyLogApiRequest;
use Admin\Adapters\Form\Type\FamilyLog\CreateFamilyLogType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

final class CreateForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public CreateFamilyLogApiRequest $initialFormData;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CreateFamilyLogType::class, $this->initialFormData);
    }
}
