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

use Inventory\UseCase\GetInventories\GetInventories;
use Shared\Adapters\Controller\Symfony\Controller\HomeController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetInventoriesController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_index';

    public function __construct(private readonly GetInventories $useCase)
    {
    }

    #[Route(path: 'inventories', name: self::ROUTE_NAME, methods: ['GET'])]
    public function __invoke(): Response
    {
        try {
            $inventories = $this->useCase->execute();
        } catch (\DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(HomeController::ROUTE_NAME);
        }
        $presenter = new GetInventoryPresenter($inventories->inventories);

        return $this->render('@inventory/index.html.twig', [
            'inventories' => $presenter->present(),
        ]);
    }
}
