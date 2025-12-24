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

namespace Inventory\Adapters\Controller\Symfony\Controller\StartInventory;

use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\UseCases\LoadArticlesAndStartInventory\LoadArticlesAndStartInventory;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class LoadArticlesAndStartInventoryController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_start';

    public function __construct(
        private readonly LoadArticlesAndStartInventory $useCase,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        path: 'inventories/{inventoryUuid}/start',
        name: self::ROUTE_NAME,
        requirements: ['inventoryUuid' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['POST']
    )]
    public function __invoke(string $inventoryUuid): Response
    {
        try {
            $this->useCase->execute(
                new LoadArticlesAndStartInventoryApiRequest(
                    ResourceUuid::fromString($inventoryUuid)
                )
            );
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }

        $this->addFlash('success', $this->translator->trans('inventory.start.success'));

        return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
    }
}
