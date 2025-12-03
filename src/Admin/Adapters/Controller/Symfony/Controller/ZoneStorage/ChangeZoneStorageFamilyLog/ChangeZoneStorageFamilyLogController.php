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

namespace Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\ChangeZoneStorageFamilyLog;

use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\GetZoneStorages\GetZoneStoragesController;
use Admin\Adapters\Form\Type\ZoneStorage\ChangeZoneStorageFamilyLogType;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Entities\Exception\FamilyLog\FamilyLogNotFoundException;
use Admin\UseCases\ZoneStorage\ChangeZoneStorageFamilyLog\ChangeZoneStorageFamilyLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class ChangeZoneStorageFamilyLogController extends AbstractController
{
    public const ROUTE_NAME = 'admin_zone_storages_change-family_log';

    public function __construct(
        private readonly ChangeZoneStorageFamilyLog $useCase,
        private readonly DoctrineFamilyLogRepository $familyLogRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        path: 'zone_storages/{zoneStorage}/change-family_log',
        name: self::ROUTE_NAME,
        requirements: ['zoneStorage' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, ZoneStorage $zoneStorage): Response
    {
        $familyLog = $this->familyLogRepository->findOneBy(['label' => $zoneStorage->familyLog()->label()]);
        if ($familyLog === null) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFoundException($zoneStorage->familyLog()->slug());
            // @codeCoverageIgnoreEnd
        }

        $form = $this->createForm(
            ChangeZoneStorageFamilyLogType::class,
            new ChangeZoneStorageFamilyLogDto($familyLog, $zoneStorage->slug()),
            [
                'action' => $this->generateUrl(
                    self::ROUTE_NAME,
                    ['zoneStorage' => $zoneStorage->uuid()]
                ),
                'attr' => ['data-turbo-frame' => '_top'],
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChangeZoneStorageFamilyLogDto $zoneStorageToUpdate */
            $zoneStorageToUpdate = $form->getData();

            try {
                $this->useCase->execute(
                    new ChangeZoneStorageFamilyLogApiRequest(
                        $zoneStorageToUpdate->familyLog,
                        $zoneStorage->slug()
                    )
                );
                // @codeCoverageIgnoreStart
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute(GetZoneStoragesController::ROUTE_NAME);
                // @codeCoverageIgnoreEnd
            }
            $this->addFlash('success', $this->translator->trans('admin.zoneStorage.changeFamilyLog.success'));

            return $this->redirectToRoute(GetZoneStoragesController::ROUTE_NAME);
        }

        return $this->render('@admin/zoneStorages/change-family_log.html.twig', [
            'form' => $form,
            'zoneStorage' => $zoneStorage,
        ]);
    }
}
