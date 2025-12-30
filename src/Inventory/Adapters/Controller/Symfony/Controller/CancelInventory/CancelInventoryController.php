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

namespace Inventory\Adapters\Controller\Symfony\Controller\CancelInventory;

use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Entities\Exception\CannotCancelCompletedInventory;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\UseCases\CancelInventory\CancelInventory;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CancelInventoryController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_cancel';

    private const string UUID_PATTERN = '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$';

    public function __construct(
        private readonly CancelInventory $useCase,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        path: 'inventories/{inventoryUuid}/cancel',
        name: self::ROUTE_NAME,
        requirements: ['inventoryUuid' => self::UUID_PATTERN],
        methods: ['POST']
    )]
    public function __invoke(string $inventoryUuid): Response
    {
        try {
            $uuid = ResourceUuid::fromString($inventoryUuid);
            $this->useCase->execute(new CancelInventoryFormRequest($uuid));

            $this->addFlash('success', $this->translator->trans('inventory.cancel.success'));

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        } catch (InventoryNotFound) {
            $this->addFlash('error', $this->translator->trans('inventory.errors.not_found'));

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        } catch (CannotCancelCompletedInventory) {
            $this->addFlash('error', $this->translator->trans('inventory.cancel.errors.cannot_cancel_completed'));

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }
    }
}
