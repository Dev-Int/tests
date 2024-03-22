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

use Admin\Adapters\Form\Type\ZoneStorage\ChangeZoneStorageFamilyLogType;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\UseCases\ZoneStorage\ChangeZoneStorageFamilyLog\ChangeZoneStorageFamilyLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ChangeZoneStorageFamilyLogController extends AbstractController
{
    public function __construct(
        private readonly ChangeZoneStorageFamilyLog $useCase,
        private readonly DoctrineFamilyLogRepository $familyLogRepository
    ) {
    }

    #[Route(
        path: 'zone_storages/{slug}/change-family_log',
        name: 'admin_zone_storages_change-family_log',
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, ZoneStorage $zoneStorage): Response
    {
        $familyLog = $this->familyLogRepository->findOneBy(['label' => $zoneStorage->familyLog()->label()]);
        $form = $this->createForm(ChangeZoneStorageFamilyLogType::class, ['familyLog' => $familyLog]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{familyLog: FamilyLog} $zoneStorageToUpdate */
            $zoneStorageToUpdate = $form->getData();

            try {
                $this->useCase->execute(
                    new ChangeZoneStorageFamilyLogApiRequest(
                        $zoneStorageToUpdate['familyLog'],
                        $zoneStorage->slug()
                    )
                );
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_zone_storages_index');
            }
            $this->addFlash('success', 'Zone storage updated');

            return $this->redirectToRoute('admin_zone_storages_index');
        }

        return $this->render('@admin/zoneStorages/change-family_log.html.twig', [
            'form' => $form,
            'zoneStorage' => $zoneStorage,
        ]);
    }
}
