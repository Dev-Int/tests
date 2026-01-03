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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\CreateArticle;

use Admin\Adapters\Controller\Symfony\Controller\Article\GetArticles\GetArticlesController;
use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Admin\Adapters\Form\Type\Article\CreateArticleType;
use Admin\Adapters\Gateway\ConfigurationService;
use Admin\Adapters\Gateway\ORM\Entity\ReadModel\Packaging;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Entities\Exception\Supplier\NoSupplierRegistered;
use Admin\Entities\Repository\FamilyLogRepository;
use Admin\Entities\Repository\SupplierRepository;
use Admin\Entities\Repository\ZoneStorageRepository;
use Admin\Entities\Unit\Unit as UnitDomain;
use Admin\UseCases\Article\CreateArticle\CreateArticle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CreateArticleController extends AbstractController
{
    public const ROUTE_NAME = 'admin_article_create';

    public function __construct(
        private readonly CreateArticle $useCase,
        private readonly ConfigurationService $configurationService,
        private readonly SupplierRepository $supplierRepository,
        private readonly FamilyLogRepository $familyLogRepository,
        private readonly ZoneStorageRepository $zoneStorageRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: 'articles/create', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->configurationService->isSupplierConfigured()) {
            $this->addFlash('error', NoSupplierRegistered::MESSAGE);

            return $this->redirectToRoute(ConfigurationController::ROUTE_NAME);
        }
        $form = $this->createForm(CreateArticleType::class, new CreateArticleInput(), [
            'action' => $this->generateUrl(self::ROUTE_NAME),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateArticleInput $article */
            $article = $form->getData();

            if ($article->supplier === null) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException('Supplier expected!');
                // @codeCoverageIgnoreEnd
            }
            $supplier = $this->supplierRepository->getBySlug($article->supplier->slug());
            if ($article->packaging === null) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException('Array expected!');
                // @codeCoverageIgnoreEnd
            }
            if ($article->tax === null) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException('Tax expected!');
                // @codeCoverageIgnoreEnd
            }
            if ($article->familyLog === null) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException('FamilyLog expected!');
                // @codeCoverageIgnoreEnd
            }
            $familyLog = $this->familyLogRepository->getBySlug($article->familyLog->slug());
            $zoneStorages = [];
            foreach ($article->zoneStorages as $zoneStorageOrm) {
                $zoneStorage = $this->zoneStorageRepository->getBySlug($zoneStorageOrm->slug());
                $zoneStorages[] = $zoneStorage;
            }
            $packaging = $this->getPackagingDomain($article->packaging);

            try {
                $this->useCase->execute(
                    new CreateArticleApiRequest(
                        $article->name,
                        $supplier,
                        $packaging,
                        $article->unitPrice ?? 0,
                        $article->tax->toDomain(),
                        $article->minStock,
                        $zoneStorages,
                        $familyLog,
                        $article->quantity ?? 0.0
                    )
                );
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute(GetArticlesController::ROUTE_NAME);
            }
            $this->addFlash('success', $this->translator->trans('admin.article.create.success'));

            return $this->redirectToRoute(GetArticlesController::ROUTE_NAME);
        }

        return $this->render('@admin/articles/create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @return array{array{UnitDomain, float}, array{UnitDomain, float}|null, array{UnitDomain, float}|null}
     */
    private function getPackagingDomain(Packaging $packaging): array
    {
        // ConsumerUnit is now mandatory
        $consumeUnit = $packaging->consumeUnit;
        if (!$consumeUnit?->unit instanceof Unit || $consumeUnit->quantity === null) {
            // @codeCoverageIgnoreStart
            throw new \InvalidArgumentException($this->translator->trans('admin.article.errors.packagingInvalid'));
            // @codeCoverageIgnoreEnd
        }

        /** @var array{UnitDomain, float} $consumeUnitRequest */
        $consumeUnitRequest = [$consumeUnit->unit->toDomain(), $consumeUnit->quantity];

        $subPackage = $packaging->subPackage;
        $subPackageRequest = null;
        if ($subPackage?->unit instanceof Unit && $subPackage->quantity !== null) {
            $subPackageRequest = [$subPackage->unit->toDomain(), $subPackage->quantity];
        }

        // Parcel is now optional
        $parcel = $packaging->parcel;
        $parcelRequest = null;
        if ($parcel?->unit instanceof Unit && $parcel->quantity !== null) {
            $parcelRequest = [$parcel->unit->toDomain(), $parcel->quantity];
        }

        return [$consumeUnitRequest, $subPackageRequest, $parcelRequest];
    }
}
