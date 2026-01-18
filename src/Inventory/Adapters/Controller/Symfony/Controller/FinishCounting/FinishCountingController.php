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

namespace Inventory\Adapters\Controller\Symfony\Controller\FinishCounting;

use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\ReviewInventoryController;
use Inventory\Entities\Exception\IncompleteInventoryCounting;
use Inventory\UseCases\FinishCounting\FinishCounting;
use Inventory\UseCases\Gateway\ZoneStorageGatewayInterface;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class FinishCountingController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_finish_counting';

    public function __construct(
        private readonly FinishCounting $useCase,
        private readonly TranslatorInterface $translator,
        private readonly ZoneStorageGatewayInterface $zoneStorageGateway,
    ) {
    }

    #[Route(
        path: '{inventoryUuid}/finish-counting',
        name: self::ROUTE_NAME,
        requirements: ['inventoryUuid' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['POST']
    )]
    public function __invoke(string $inventoryUuid): Response
    {
        try {
            $this->useCase->execute(
                new FinishCountingApiRequest(
                    ResourceUuid::fromString($inventoryUuid)
                )
            );
        } catch (IncompleteInventoryCounting $exception) {
            $zones = $this->zoneStorageGateway->provideAll($exception->zonesWithUncountedItems());
            $zoneNames = array_map(
                static fn ($zone): string => $zone->name->toString(),
                $zones
            );
            $message = $this->translator->trans('inventory.finish_counting.incomplete_zones', [
                '%zones%' => implode(', ', $zoneNames),
            ]);
            $this->addFlash('error', $message);

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }

        $this->addFlash('success', $this->translator->trans('inventory.finish_counting.success'));

        return $this->redirectToRoute(
            ReviewInventoryController::ROUTE_NAME,
            ['inventoryUuid' => $inventoryUuid]
        );
    }
}
