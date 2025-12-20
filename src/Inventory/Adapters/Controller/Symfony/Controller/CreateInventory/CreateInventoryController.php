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

namespace Inventory\Adapters\Controller\Symfony\Controller\CreateInventory;

use Admin\Contracts\Services\Provider\ConfigurationServiceProvider;
use Admin\Contracts\Services\Provider\Exception\NoArticleRegistered;
use Admin\Contracts\Services\Provider\ZoneStorage\ZoneStorageProvider;
use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Adapters\Form\Type\CreateInventoryType;
use Inventory\Entities\ReadModel\ZoneStorage;
use Inventory\UseCases\CreateInventory\CreateInventory;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CreateInventoryController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_create';

    public function __construct(
        private readonly CreateInventory $useCase,
        private readonly ConfigurationServiceProvider $configurationService,
        private readonly TranslatorInterface $translator,
        private readonly ZoneStorageProvider $zoneStorageProvider,
    ) {
    }

    #[Route(path: 'inventories/create', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->configurationService->isArticleConfigured()) {
            $this->addFlash('error', NoArticleRegistered::MESSAGE);

            return $this->redirectToRoute(ConfigurationServiceProvider::ROUTE_NAME);
        }
        $form = $this->createForm(CreateInventoryType::class, new CreateInventoryInput(), [
            'action' => $this->generateUrl(self::ROUTE_NAME),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateInventoryInput $inventory */
            $inventory = $form->getData();

            if ($inventory->date === null) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException('Date expected');
                // @codeCoverageIgnoreEnd
            }

            $zoneStorages = [];

            // Si aucune zone sélectionnée, on prend toutes les zones
            if ($inventory->zoneStorages === []) {
                $allZones = $this->zoneStorageProvider->provideAll();
                foreach ($allZones as $zone) {
                    $zoneStorages[] = new ZoneStorage($zone->uuid, $zone->label);
                }
            } else {
                foreach ($inventory->zoneStorages as $zoneStorage) {
                    $zone = $this->zoneStorageProvider->provide(ResourceUuid::fromString($zoneStorage));
                    $zoneStorages[] = new ZoneStorage($zone->uuid, $zone->label);
                }
            }

            try {
                $this->useCase->execute(
                    new CreateInventoryApiRequest(
                        ResourceUuid::generate(),
                        $inventory->date,
                        $zoneStorages
                    )
                );
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
            }
            $this->addFlash('success', $this->translator->trans('inventory.create.success'));

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }

        return $this->render('@inventory/create.html.twig', [
            'form' => $form,
        ]);
    }
}
