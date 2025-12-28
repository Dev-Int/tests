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

namespace Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory;

use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Entities\Repository\InventoryRepository;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ReviewInventoryController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_review';

    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
    ) {
    }

    #[Route(
        path: 'inventories/{inventoryUuid}/review',
        name: self::ROUTE_NAME,
        requirements: ['inventoryUuid' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET']
    )]
    public function __invoke(string $inventoryUuid): Response
    {
        try {
            $inventory = $this->inventoryRepository->getByUuid(
                ResourceUuid::fromString($inventoryUuid)
            );
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }

        $itemsWithDiscrepancies = $inventory->items()->getItemsWithDiscrepancies();
        $presenter = new ReviewInventoryPresenter($itemsWithDiscrepancies);

        return $this->render('@inventory/review.html.twig', [
            'inventory' => $inventory,
            'discrepancyCount' => \count($itemsWithDiscrepancies),
            'items' => $presenter->present(),
        ]);
    }
}
