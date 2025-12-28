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
use Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\Input\ItemChoice;
use Inventory\Adapters\Form\Type\ReviewInventoryType;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\UseCases\ReviewDiscrepancies\ReviewDiscrepancies;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ReviewInventoryController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_review';

    public function __construct(
        private readonly ReviewDiscrepancies $useCase,
        private readonly InventoryRepository $inventoryRepository,
    ) {
    }

    #[Route(
        path: 'inventories/{inventoryUuid}/review',
        name: self::ROUTE_NAME,
        requirements: ['inventoryUuid' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, string $inventoryUuid): Response
    {
        try {
            $uuid = ResourceUuid::fromString($inventoryUuid);
            $inventory = $this->inventoryRepository->getByUuid($uuid);
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }

        $itemsWithDiscrepancies = $inventory->items()->getItemsWithDiscrepancies();
        $presenter = new ReviewInventoryPresenter($itemsWithDiscrepancies);
        $presentedItems = $presenter->present();

        $formItems = array_map(
            static fn (DiscrepancyItemResult $item): ItemChoice => new ItemChoice(
                $item->identifier,
                $item->articleName,
                $item->isReviewed,
            ),
            $presentedItems
        );

        $form = $this->createForm(ReviewInventoryType::class, null, [
            'items' => $formItems,
            'action' => $this->generateUrl(
                self::ROUTE_NAME,
                ['inventoryUuid' => $inventoryUuid]
            ),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{reviewed_items: array<ItemChoice>|null} $data */
            $data = $form->getData();
            $selectedItems = array_map(
                static fn (ItemChoice $item): string => $item->identifier,
                $data['reviewed_items'] ?? []
            );

            if ($selectedItems === []) {
                $this->addFlash('warning', 'inventory.review.no_items_selected');

                return $this->redirectToRoute(
                    self::ROUTE_NAME,
                    ['inventoryUuid' => $inventoryUuid]
                );
            }

            $formRequest = new ReviewInventoryFormRequest(
                inventoryUuid: $uuid,
                selectedItems: $selectedItems,
            );

            try {
                $response = $this->useCase->execute($formRequest);

                $this->addFlash(
                    'success',
                    'inventory.review.items_reviewed'
                );

                $unreviewedItems = array_filter(
                    $response->inventory->items()->getItemsWithDiscrepancies(),
                    static fn ($item) => !$item->isReviewed()
                );

                if ($unreviewedItems === []) {
                    return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
                }
            } catch (DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render('@inventory/review.html.twig', [
            'inventory' => $inventory,
            'discrepancyCount' => \count($itemsWithDiscrepancies),
            'items' => $presentedItems,
            'form' => $form,
        ]);
    }
}
