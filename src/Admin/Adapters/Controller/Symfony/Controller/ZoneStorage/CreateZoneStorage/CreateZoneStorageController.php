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

use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\GetZoneStorages\GetZoneStoragesController;
use Admin\Adapters\Form\Type\ZoneStorage\ZoneStorageType;
use Admin\Adapters\Gateway\ConfigurationService;
use Admin\Entities\Exception\FamilyLog\NoFamilyLogRegisteredException;
use Admin\Entities\Exception\ZoneStorage\ZoneStorageAlreadyExists;
use Admin\UseCases\ZoneStorage\CreateZoneStorage\CreateZoneStorage;
use Ramsey\Uuid\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CreateZoneStorageController extends AbstractController
{
    public const ROUTE_NAME = 'admin_zone_storages_create';

    public function __construct(
        private readonly CreateZoneStorage $useCase,
        private readonly ConfigurationService $configurationService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: 'zone_storages/create', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->configurationService->isFamilyLogConfigured()) {
            $this->addFlash('error', NoFamilyLogRegisteredException::MESSAGE);

            return $this->redirectToRoute(ConfigurationController::ROUTE_NAME);
        }

        $form = $this->createForm(ZoneStorageType::class, new CreateZoneStorageDto(), [
            'action' => $this->generateUrl(self::ROUTE_NAME),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateZoneStorageDto $zoneStorage */
            $zoneStorage = $form->getData();

            if ($zoneStorage->familyLog === null) {
                // @codeCoverageIgnoreStart
                throw new InvalidArgumentException('FamilyLog expected!');
                // @codeCoverageIgnoreEnd
            }

            try {
                $this->useCase->execute(
                    new CreateZoneStorageApiRequest(
                        $zoneStorage->label,
                        $zoneStorage->familyLog->toDomain()
                    )
                );
            } catch (ZoneStorageAlreadyExists $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute(GetZoneStoragesController::ROUTE_NAME);
            }
            $this->addFlash('success', $this->translator->trans('admin.zoneStorage.create.success'));

            return $this->redirectToRoute(GetZoneStoragesController::ROUTE_NAME, [], Response::HTTP_FOUND);
        }

        return $this->render('@admin/zoneStorages/create.html.twig', [
            'form' => $form,
        ]);
    }
}
