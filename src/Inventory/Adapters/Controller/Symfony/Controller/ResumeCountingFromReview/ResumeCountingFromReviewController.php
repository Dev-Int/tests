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

namespace Inventory\Adapters\Controller\Symfony\Controller\ResumeCountingFromReview;

use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Adapters\Controller\Symfony\Controller\RecordRealStockForZone\RecordRealStockForZoneController;
use Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\ReviewInventoryController;
use Inventory\Entities\Exception\InvalidStatusTransition;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\UseCases\ResumeCountingFromReview\ResumeCountingFromReview;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[IsGranted('ROLE_INVENTORY_MANAGER')]
final class ResumeCountingFromReviewController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_resume_counting';

    private const string UUID_PATTERN = '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$';

    public function __construct(
        private readonly ResumeCountingFromReview $useCase,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        path: '{inventoryUuid}/zones/{zoneStorageUuid}/resume-counting',
        name: self::ROUTE_NAME,
        requirements: [
            'inventoryUuid' => self::UUID_PATTERN,
            'zoneStorageUuid' => self::UUID_PATTERN,
        ],
        methods: ['POST']
    )]
    public function __invoke(string $inventoryUuid, string $zoneStorageUuid): Response
    {
        try {
            $inventoryUuidVo = ResourceUuid::fromString($inventoryUuid);
            $zoneStorageUuidVo = ResourceUuid::fromString($zoneStorageUuid);

            $this->useCase->execute(
                new ResumeCountingFromReviewFormRequest($inventoryUuidVo, $zoneStorageUuidVo)
            );

            $this->addFlash('success', $this->translator->trans('inventory.resume_counting.success'));

            return $this->redirectToRoute(
                RecordRealStockForZoneController::ROUTE_NAME,
                [
                    'inventoryUuid' => $inventoryUuid,
                    'zoneStorageUuid' => $zoneStorageUuid,
                ]
            );
        } catch (InventoryNotFound) {
            $this->addFlash('error', $this->translator->trans('inventory.errors.not_found'));

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        } catch (InvalidStatusTransition) {
            $this->addFlash('error', $this->translator->trans('inventory.resume_counting.errors.cannot_resume'));

            return $this->redirectToRoute(ReviewInventoryController::ROUTE_NAME, ['inventoryUuid' => $inventoryUuid]);
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(ReviewInventoryController::ROUTE_NAME, ['inventoryUuid' => $inventoryUuid]);
        }
    }
}
