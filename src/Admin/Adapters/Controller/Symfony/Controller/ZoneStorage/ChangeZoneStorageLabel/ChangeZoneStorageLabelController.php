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

namespace Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\ChangeZoneStorageLabel;

use Admin\Adapters\Form\Type\ZoneStorage\ChangeLabelZoneStorageType;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\UseCases\ZoneStorage\ChangeZoneStorageLabel\ChangeZoneStorageLabel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ChangeZoneStorageLabelController extends AbstractController
{
    public function __construct(private readonly ChangeZoneStorageLabel $useCase)
    {
    }

    #[Route(
        path: 'zone_storages/{slug}/change-label',
        name: 'admin_zone_storages_change-label',
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, ZoneStorage $zoneStorage): Response
    {
        $form = $this->createForm(
            ChangeLabelZoneStorageType::class,
            new ChangeZoneStorageLabelApiRequest($zoneStorage->label(), $zoneStorage->slug())
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChangeZoneStorageLabelApiRequest $zoneStorageToUpdate */
            $zoneStorageToUpdate = $form->getData();

            try {
                $this->useCase->execute($zoneStorageToUpdate);
                // @codeCoverageIgnoreStart
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_zone_storages_index');
                // @codeCoverageIgnoreEnd
            }
            $this->addFlash('success', 'Zone storage updated');

            return $this->redirectToRoute('admin_zone_storages_index');
        }

        return $this->render('@admin/zoneStorages/change-label.html.twig', [
            'form' => $form,
            'zoneStorage' => $zoneStorage,
        ]);
    }
}
