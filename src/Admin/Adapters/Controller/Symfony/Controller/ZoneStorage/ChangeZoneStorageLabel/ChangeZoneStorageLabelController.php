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
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class ChangeZoneStorageLabelController extends AbstractController
{
    public function __construct(
        private readonly ChangeZoneStorageLabel $useCase,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(
        path: 'zone_storages/{zoneStorage}/change-label',
        name: 'admin_zone_storages_change-label',
        requirements: ['zoneStorage' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, ZoneStorage $zoneStorage): Response
    {
        $form = $this->createForm(
            ChangeLabelZoneStorageType::class,
            new ChangeZoneStorageLabelApiRequest($zoneStorage->label(), $zoneStorage->slug()),
            [
                'action' => $this->generateUrl(
                    'admin_zone_storages_change-label',
                    ['zoneStorage' => $zoneStorage->uuid()]
                ),
                'attr' => ['data-turbo-frame' => '_top'],
            ]
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
            $this->addFlash('success', $this->translator->trans('admin.zoneStorage.changeLabel.success'));

            return $this->redirectToRoute('admin_zone_storages_index');
        }

        return $this->render('@admin/zoneStorages/change-label.html.twig', [
            'form' => $form,
            'zoneStorage' => $zoneStorage,
        ]);
    }
}
