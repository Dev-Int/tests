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

namespace Admin\Twig\Components\Form\ZoneStorage;

use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\ChangeZoneStorageLabel\ChangeZoneStorageLabelApiRequest;
use Admin\Adapters\Form\Type\ZoneStorage\ChangeLabelZoneStorageType;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ChangeLabelForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ChangeZoneStorageLabelApiRequest $initialFormData;

    #[LiveProp]
    public ZoneStorage $zoneStorage;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ChangeLabelZoneStorageType::class, $this->initialFormData);
    }
}
