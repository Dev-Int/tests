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

use Admin\Adapters\Form\Type\Article\CreateArticleType;
use Admin\Adapters\Gateway\ConfigurationService;
use Admin\Adapters\Gateway\ORM\Entity\ReadModel\Packaging;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Entities\Exception\NoSupplierRegisteredException;
use Admin\Entities\Unit\Unit as UnitDomain;
use Admin\UseCases\Article\CreateArticle\CreateArticle;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\Gateway\SupplierRepository;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CreateArticleController extends AbstractController
{
    public function __construct(
        private readonly CreateArticle $useCase,
        private readonly ConfigurationService $configurationService,
        private readonly SupplierRepository $supplierRepository,
        private readonly FamilyLogRepository $familyLogRepository,
        private readonly ZoneStorageRepository $zoneStorageRepository
    ) {
    }

    #[Route(path: 'articles/create', name: 'admin_article_create', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->configurationService->isSupplierConfigured()) {
            $this->addFlash('error', NoSupplierRegisteredException::MESSAGE);

            return $this->redirectToRoute('admin_configure');
        }
        $form = $this->createForm(CreateArticleType::class, new CreateArticleInput());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateArticleInput $article */
            $article = $form->getData();

            if ($article->supplier === null) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException('Supplier expected!');
                // @codeCoverageIgnoreEnd
            }
            $supplier = $this->supplierRepository->findBySlug($article->supplier->slug());
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
            $familyLog = $this->familyLogRepository->findBySlug($article->familyLog->slug());
            $zoneStorages = [];
            foreach ($article->zoneStorages as $zoneStorageOrm) {
                $zoneStorage = $this->zoneStorageRepository->findBySlug($zoneStorageOrm->slug());
                $zoneStorages[] = $zoneStorage;
            }
            $packaging = $this->getPackagingDomain($article->packaging);

            try {
                $this->useCase->execute(
                    new CreateArticleApiRequest(
                        $article->name,
                        $supplier,
                        $packaging,
                        $article->amount,
                        $article->tax->toDomain(),
                        $article->minStock,
                        $zoneStorages,
                        $familyLog,
                        $article->quantity ?? 0.0
                    )
                );
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_articles_index');
            }
            $this->addFlash('success', 'Article created');

            return $this->redirectToRoute('admin_articles_index');
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
        $parcel = $packaging->parcel;
        if (!$parcel?->unit instanceof Unit || $parcel->quantity === null) {
            throw new \InvalidArgumentException('parcel should have unit and quantity');
        }

        /** @var array{UnitDomain, float} $parcelRequest */
        $parcelRequest = [$parcel->unit->toDomain(), $parcel->quantity];
        $subPackage = $packaging->subPackage;
        $subPackageRequest = null;
        if ($subPackage?->unit instanceof Unit && $subPackage->quantity !== null) {
            $subPackageRequest = [$subPackage->unit->toDomain(), $subPackage->quantity];
        }
        $consumeUnit = $packaging->consumeUnit;
        $consumeUnitRequest = null;
        if ($consumeUnit?->unit instanceof Unit && $consumeUnit->quantity !== null) {
            $consumeUnitRequest = [$consumeUnit->unit->toDomain(), $consumeUnit->quantity];
        }

        return [$parcelRequest, $subPackageRequest, $consumeUnitRequest];
    }
}
