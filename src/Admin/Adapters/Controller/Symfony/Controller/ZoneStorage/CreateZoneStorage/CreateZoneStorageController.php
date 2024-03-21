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

namespace Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\CreateZoneStorage;

use Admin\Adapters\Form\Type\ZoneStorage\ZoneStorageType;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Entities\Exception\ZoneStorageAlreadyExistsException;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\ZoneStorage\CreateZoneStorage\CreateZoneStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CreateZoneStorageController extends AbstractController
{
    public function __construct(
        private readonly CreateZoneStorage $useCase,
        private readonly FamilyLogRepository $familyLogRepository
    ) {
    }

    #[Route(path: 'zone_storages/create', name: 'admin_zone_storages_create', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(ZoneStorageType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{label: string, familyLog: FamilyLog} $zoneStorage */
            $zoneStorage = $form->getData();
            $familyLog = $this->familyLogRepository->findBySlug($zoneStorage['familyLog']->slug());

            try {
                $this->useCase->execute(
                    new CreateZoneStorageApiRequest(
                        $zoneStorage['label'],
                        $familyLog
                    )
                );
            } catch (ZoneStorageAlreadyExistsException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_zone_storages_index');
            }
            $this->addFlash('success', 'Zone storage created');

            return $this->redirectToRoute('admin_zone_storages_index', [], Response::HTTP_FOUND);
        }

        return $this->render('@admin/zoneStorages/create.html.twig', [
            'form' => $form,
        ]);
    }
}
