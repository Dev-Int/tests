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

use Shared\Adapters\Gateway\Pagination\Pagination;
use Inventory\UseCases\GetInventories\GetInventories;
use Shared\Adapters\Attribute\RequireApplicationReady;
use Shared\Adapters\Controller\Symfony\Controller\HomeController;
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
        $page = $request->query->getInt('page', Pagination::DEFAULT_PAGE);
        $itemsPerPage = $request->query->getInt('itemsPerPage', Pagination::DEFAULT_ITEMS_PER_PAGE);

        try {
            $response = $this->useCase->execute(new GetInventoriesApiRequest($page, $itemsPerPage));
        } catch (\DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(HomeController::ROUTE_NAME);
        }

        $presenter = new GetInventoryPresenter($response->inventories);
        $pagination = new Pagination($response->inventories->count(), $page, $itemsPerPage);

        return $this->render('@inventory/index.html.twig', [
            'inventories' => $presenter->present(),
            'pagination' => $pagination,
        ]);
    }
}
