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

namespace App\Twig\Components\Form\ZoneStorage;

use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\CreateZoneStorage\CreateZoneStorageApiRequest;
use Admin\Adapters\Form\Type\ZoneStorage\ZoneStorageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CreateForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public CreateZoneStorageApiRequest $initialFormData;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ZoneStorageType::class, $this->initialFormData);
    }
}
