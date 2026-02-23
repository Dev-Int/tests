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

use Admin\Contracts\Services\Provider\ZoneStorage\ZoneStorageProvider;
use Auth\Contracts\Attribute\RequireRole;
use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\Input\ItemChoice;
use Inventory\Adapters\Form\Type\ReviewInventoryType;
use Inventory\Entities\Exception\ArticleNotFoundInInventory;
use Inventory\Entities\Exception\CannotReviewItemWithoutDiscrepancy;
use Inventory\Entities\Exception\NoItemsSelectedForReview;
use Inventory\Entities\Inventory;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\UseCases\ReviewDiscrepancies\ReviewDiscrepancies;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[RequireRole(role: 'ROLE_INVENTORY_MANAGER')]
final class ReviewInventoryController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_review';

    private const string UUID_PATTERN = '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$';

    public function __construct(
        private readonly ReviewDiscrepancies $useCase,
        private readonly InventoryRepository $inventoryRepository,
        private readonly TranslatorInterface $translator,
        private readonly ZoneStorageProvider $zoneStorageProvider,
    ) {
    }

    #[Route(
        path: '{inventoryUuid}/review',
        name: self::ROUTE_NAME,
        requirements: ['inventoryUuid' => self::UUID_PATTERN],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, string $inventoryUuid): Response
    {
        $inventory = $this->getInventoryOrNull($inventoryUuid);
        if (!$inventory instanceof Inventory) {
            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }

        $itemsWithDiscrepancies = $inventory->items()->getItemsWithDiscrepancies();
        $presenter = new ReviewInventoryPresenter($itemsWithDiscrepancies);
        $presentedItems = $presenter->present();
        $formItems = $this->buildFormItems($presentedItems);

        $form = $this->createReviewForm($inventoryUuid, $formItems);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleFormSubmission($form, $inventoryUuid);
        }

        $zonesWithUnreviewedItems = $this->buildZonesWithLabels(
            $presenter->getZonesWithUnreviewedItems()
        );

        return $this->renderReviewForm($inventory, $presentedItems, $form, $zonesWithUnreviewedItems);
    }

    private function getInventoryOrNull(string $inventoryUuid): ?Inventory
    {
        try {
            $uuid = ResourceUuid::fromString($inventoryUuid);

            return $this->inventoryRepository->getByUuid($uuid);
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return null;
        }
    }

    /**
     * @param array<DiscrepancyItemResult> $presentedItems
     *
     * @return array<ItemChoice>
     */
    private function buildFormItems(array $presentedItems): array
    {
        return array_map(
            static fn (DiscrepancyItemResult $item): ItemChoice => new ItemChoice(
                $item->identifier,
                $item->articleName,
                $item->isReviewed,
            ),
            $presentedItems
        );
    }

    /**
     * @param array<ItemChoice> $formItems
     */
    private function createReviewForm(string $inventoryUuid, array $formItems): FormInterface
    {
        return $this->createForm(ReviewInventoryType::class, null, [
            'items' => $formItems,
            'action' => $this->generateUrl(self::ROUTE_NAME, ['inventoryUuid' => $inventoryUuid]),
        ]);
    }

    private function handleFormSubmission(FormInterface $form, string $inventoryUuid): Response
    {
        $selectedItems = $this->extractSelectedItems($form);
        $uuid = ResourceUuid::fromString($inventoryUuid);

        try {
            $response = $this->useCase->execute(
                new ReviewInventoryFormRequest($uuid, $selectedItems)
            );

            $this->addFlash('success', $this->translator->trans('inventory.review.items_reviewed'));

            return $this->determineRedirectAfterSuccess($response->inventory, $inventoryUuid);
        } catch (NoItemsSelectedForReview) {
            $this->addFlash('warning', $this->translator->trans('inventory.review.no_items_selected'));

            return $this->redirectToRoute(self::ROUTE_NAME, ['inventoryUuid' => $inventoryUuid]);
        } catch (CannotReviewItemWithoutDiscrepancy) {
            $this->addFlash('error', $this->translator->trans('inventory.review.errors.no_discrepancy'));

            return $this->redirectToRoute(self::ROUTE_NAME, ['inventoryUuid' => $inventoryUuid]);
        } catch (ArticleNotFoundInInventory) {
            $this->addFlash('error', $this->translator->trans('inventory.review.errors.article_not_found'));

            return $this->redirectToRoute(self::ROUTE_NAME, ['inventoryUuid' => $inventoryUuid]);
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(self::ROUTE_NAME, ['inventoryUuid' => $inventoryUuid]);
        }
    }

    /**
     * @return array<string>
     */
    private function extractSelectedItems(FormInterface $form): array
    {
        /** @var array{reviewed_items: array<ItemChoice>|null} $data */
        $data = $form->getData();

        return array_map(
            static fn (ItemChoice $item): string => $item->identifier,
            $data['reviewed_items'] ?? []
        );
    }

    /**
     * Détermine la redirection après une soumission de révision réussie.
     *
     * Décision de conception : quand des items restent non révisés, on redirige vers la même page
     * plutôt que de filtrer les items déjà révisés. Cela permet aux utilisateurs de :
     * - Voir le contexte complet des écarts (révisés + non révisés)
     * - Suivre la progression visuellement avec l'indicateur "✅ Révisé"
     * - Traiter les items par lots sans perdre la vue d'ensemble
     *
     * Les items révisés restent visibles mais non modifiables, fournissant une piste d'audit claire.
     */
    private function determineRedirectAfterSuccess(Inventory $inventory, string $inventoryUuid): Response
    {
        $unreviewedItems = array_filter(
            $inventory->items()->getItemsWithDiscrepancies(),
            static fn ($item) => !$item->isReviewed()
        );

        if ($unreviewedItems === []) {
            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }

        return $this->redirectToRoute(self::ROUTE_NAME, ['inventoryUuid' => $inventoryUuid]);
    }

    /**
     * Construit les zones avec leurs labels depuis ZoneStorageProvider.
     *
     * @param array<string> $zoneUuids
     *
     * @return array<ZoneWithUnreviewedItemsResult>
     */
    private function buildZonesWithLabels(array $zoneUuids): array
    {
        if ($zoneUuids === []) {
            return [];
        }

        $resourceUuids = array_map(
            static fn (string $uuid): ResourceUuid => ResourceUuid::fromString($uuid),
            $zoneUuids
        );

        $zoneStorages = $this->zoneStorageProvider->provideAll($resourceUuids);

        $results = [];
        foreach ($zoneStorages as $zoneStorage) {
            $results[] = new ZoneWithUnreviewedItemsResult(
                uuid: $zoneStorage->uuid->toString(),
                label: $zoneStorage->label->toString(),
            );
        }

        return $results;
    }

    /**
     * @param array<DiscrepancyItemResult>         $presentedItems
     * @param array<ZoneWithUnreviewedItemsResult> $zonesWithUnreviewedItems
     */
    private function renderReviewForm(
        Inventory $inventory,
        array $presentedItems,
        FormInterface $form,
        array $zonesWithUnreviewedItems,
    ): Response {
        return $this->render('@inventory/review.html.twig', [
            'inventory' => $inventory,
            'discrepancyCount' => \count($presentedItems),
            'items' => $presentedItems,
            'form' => $form,
            'zonesWithUnreviewedItems' => $zonesWithUnreviewedItems,
        ]);
    }
}
