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

use Admin\Adapters\Form\Type\FamilyLog\AssignParentFamilyLogType;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class AssignParentForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    /** @var array{parent: FamilyLog, uuid: string} */
    #[LiveProp]
    public array $initialFormData;

    #[LiveProp]
    public FamilyLog $familyLog;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(AssignParentFamilyLogType::class, $this->initialFormData);
    }
}
