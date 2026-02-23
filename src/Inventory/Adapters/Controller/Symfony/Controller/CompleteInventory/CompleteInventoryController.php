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

namespace Inventory\Adapters\Controller\Symfony\Controller\CompleteInventory;

use Auth\Contracts\Attribute\RequireRole;
use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Entities\Exception\InvalidStatusTransition;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\Entities\Exception\UnreviewedDiscrepancies;
use Inventory\UseCases\CompleteInventory\CompleteInventory;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[RequireRole(role: 'ROLE_INVENTORY_MANAGER')]
final class CompleteInventoryController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_complete';

    private const string UUID_PATTERN = '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$';

    public function __construct(
        private readonly CompleteInventory $useCase,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        path: '{inventoryUuid}/complete',
        name: self::ROUTE_NAME,
        requirements: ['inventoryUuid' => self::UUID_PATTERN],
        methods: ['POST']
    )]
    public function __invoke(string $inventoryUuid): Response
    {
        try {
            $uuid = ResourceUuid::fromString($inventoryUuid);
            $response = $this->useCase->execute(new CompleteInventoryFormRequest($uuid));

            $this->addFlash('success', $this->translator->trans('inventory.complete.success', [
                '%articles%' => $response->articlesUpdated,
            ]));

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        } catch (InventoryNotFound) {
            $this->addFlash('error', $this->translator->trans('inventory.errors.not_found'));

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        } catch (InvalidStatusTransition) {
            $this->addFlash('error', $this->translator->trans('inventory.complete.errors.invalid_status'));

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        } catch (UnreviewedDiscrepancies $exception) {
            $this->addFlash('error', $this->translator->trans('inventory.complete.errors.unreviewed_discrepancies', [
                '%count%' => \count($exception->unreviewedItems()),
            ]));

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }
    }
}
