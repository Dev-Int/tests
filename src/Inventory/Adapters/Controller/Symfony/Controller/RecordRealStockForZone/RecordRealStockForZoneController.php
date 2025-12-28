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

namespace Inventory\Adapters\Controller\Symfony\Controller\RecordRealStockForZone;

use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Entities\Exception\NumericExpected;
use Inventory\Entities\InventoryItem;
use Inventory\Entities\ReadModel\ArticleData;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\RealStockComponents;
use Inventory\UseCases\Gateway\ZoneStorageGatewayInterface;
use Inventory\UseCases\RecordRealStockForZone\RecordRealStockForZone;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class RecordRealStockForZoneController extends AbstractController
{
    public const string ROUTE_NAME = 'inventory_zone_record_stock';

    private const string UUID_PATTERN = '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$';

    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly RecordRealStockForZone $useCase,
        private readonly ZoneStorageGatewayInterface $zoneStorageGateway,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        path: 'inventories/{inventoryUuid}/zones/{zoneStorageUuid}/record',
        name: self::ROUTE_NAME,
        requirements: [
            'inventoryUuid' => self::UUID_PATTERN,
            'zoneStorageUuid' => self::UUID_PATTERN,
        ],
        methods: ['GET', 'POST']
    )]
    public function __invoke(
        Request $request,
        string $inventoryUuid,
        string $zoneStorageUuid,
    ): Response {
        $inventoryUuidVo = ResourceUuid::fromString($inventoryUuid);
        $zoneStorageUuidVo = ResourceUuid::fromString($zoneStorageUuid);

        try {
            $inventory = $this->inventoryRepository->getByUuid($inventoryUuidVo);
            $zoneStorage = $this->zoneStorageGateway->provide($zoneStorageUuidVo);
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }

        $items = $inventory->items()->filterByZone($zoneStorageUuidVo);

        if ($request->isMethod(Request::METHOD_POST)) {
            return $this->handlePost(
                $request,
                $inventoryUuidVo,
                $zoneStorageUuidVo,
                $items
            );
        }

        return $this->renderForm(
            $items,
            $zoneStorage->name->toString(),
            $inventoryUuid,
            $zoneStorageUuid
        );
    }

    /**
     * @param array<InventoryItem> $items
     */
    private function handlePost(
        Request $request,
        ResourceUuid $inventoryUuid,
        ResourceUuid $zoneStorageUuid,
        array $items,
    ): Response {
        // Validate all parcel fields are filled
        $missingArticles = $this->validateAllFieldsFilled($request, $items);
        if ($missingArticles !== []) {
            $this->addFlash('error', $this->translator->trans(
                'inventory.zone.record.error_missing_fields',
                ['%articles%' => implode(', ', $missingArticles)]
            ));

            return $this->redirectToRoute(
                self::ROUTE_NAME,
                [
                    'inventoryUuid' => $inventoryUuid->toString(),
                    'zoneStorageUuid' => $zoneStorageUuid->toString(),
                ]
            );
        }

        $articlesData = $this->buildArticlesData($request, $items);

        try {
            $this->useCase->execute(
                new RecordRealStockForZoneApiRequest(
                    $inventoryUuid,
                    $zoneStorageUuid,
                    $articlesData
                )
            );
        } catch (DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
        }

        $this->addFlash('success', $this->translator->trans('inventory.zone.record.success'));

        return $this->redirectToRoute(GetInventoriesController::ROUTE_NAME);
    }

    /**
     * Validates that all parcel fields are filled.
     *
     * Note: Only the parcel is required. SubPackage and ConsumerUnit fields
     * are optional and default to 0 if empty (see buildArticlesData).
     * This is intentional: users may count only full parcels.
     *
     * @param array<InventoryItem> $items
     *
     * @return array<string> List of article names with missing parcel values
     */
    private function validateAllFieldsFilled(Request $request, array $items): array
    {
        $missingArticles = [];

        foreach ($items as $item) {
            $articleSlug = $item->articleName()->slugify();
            $parcelValue = $this->getNumericValue($request, "real_stock_{$articleSlug}_parcel");

            if ($parcelValue === null) {
                $missingArticles[] = $item->articleName()->toString();
            }
        }

        return $missingArticles;
    }

    /**
     * @param array<InventoryItem> $items
     *
     * @return array<ArticleData>
     */
    private function buildArticlesData(Request $request, array $items): array
    {
        $articlesData = [];

        foreach ($items as $item) {
            $articleSlug = $item->articleName()->slugify();

            $parcelValue = $this->getNumericValue($request, "real_stock_{$articleSlug}_parcel");
            $subPackageValue = $this->getNumericValue($request, "real_stock_{$articleSlug}_sub_package");
            $consumerUnitValue = $this->getNumericValue($request, "real_stock_{$articleSlug}_consumer_unit");

            \assert($parcelValue !== null, 'Parcel value should be validated before buildArticlesData');

            $components = RealStockComponents::fromUnits(
                $parcelValue,
                $subPackageValue ?? 0.0,
                $consumerUnitValue ?? 0.0
            );
            $realStock = $item->packaging()->calculateTotalFromComponents($components);

            $articlesData[] = new ArticleData(
                articleUuid: $item->article(),
                realStock: $realStock,
                realStockComponents: $components,
            );
        }

        return $articlesData;
    }

    private function getNumericValue(Request $request, string $key): ?float
    {
        $value = $request->request->get($key);
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value)) {
            throw new NumericExpected($key, $value);
        }

        return (float) $value;
    }

    /**
     * @param array<InventoryItem> $items
     */
    private function renderForm(
        array $items,
        string $zoneName,
        string $inventoryUuid,
        string $zoneStorageUuid,
    ): Response {
        $presenter = new RecordRealStockForZonePresenter($items);

        return $this->render('@inventory/record_stock.html.twig', [
            'items' => $presenter->present(),
            'zoneName' => $zoneName,
            'inventoryUuid' => $inventoryUuid,
            'zoneStorageUuid' => $zoneStorageUuid,
        ]);
    }
}
