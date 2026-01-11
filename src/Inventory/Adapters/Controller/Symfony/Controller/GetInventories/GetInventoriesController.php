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

namespace Inventory\Adapters\Controller\Symfony\Controller\GetInventories;

use Inventory\Adapters\Form\Type\InventoryFilterType;
use Inventory\UseCases\GetInventories\GetInventories;
use Shared\Adapters\Attribute\RequireApplicationReady;
use Shared\Adapters\Controller\Symfony\Controller\HomeController;
use Shared\Adapters\Gateway\Pagination\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RequireApplicationReady]
final class GetInventoriesController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_index';

    public function __construct(
        private readonly GetInventories $useCase,
    ) {
    }

    #[Route(path: 'inventories', name: self::ROUTE_NAME, methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $filterForm = $this->createForm(InventoryFilterType::class);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && !$filterForm->isValid()) {
            return $this->render('@inventory/index.html.twig', [
                'inventories' => [],
                'pagination' => new Pagination(0, 1, Pagination::DEFAULT_ITEMS_PER_PAGE),
                'filterForm' => $filterForm->createView(),
            ]);
        }

        $dateForm = $filterForm->get('date');

        /** @var string|null $status */
        $status = $filterForm->get('status')->getData();

        /** @var \DateTimeImmutable|null $dateAfter */
        $dateAfter = $dateForm->get('after')->getData();

        /** @var \DateTimeImmutable|null $dateBefore */
        $dateBefore = $dateForm->get('before')->getData();

        /** @var string|null $zoneStorage */
        $zoneStorage = $filterForm->get('zoneStorage')->getData();

        $apiRequest = new GetInventoriesApiRequest(
            page: $request->query->getInt('page', Pagination::DEFAULT_PAGE),
            itemsPerPage: $request->query->getInt('itemsPerPage', Pagination::DEFAULT_ITEMS_PER_PAGE),
            status: $status,
            dateAfter: $dateAfter,
            dateBefore: $dateBefore,
            zoneStorage: $zoneStorage,
        );

        try {
            $response = $this->useCase->execute($apiRequest);
        } catch (\DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(HomeController::ROUTE_NAME);
        }

        $presenter = new GetInventoryPresenter($response->inventories);
        $pagination = new Pagination(
            $response->inventories->count(),
            $apiRequest->page,
            $apiRequest->itemsPerPage,
        );

        return $this->render('@inventory/index.html.twig', [
            'inventories' => $presenter->present(),
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView(),
        ]);
    }
}
