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
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Entities\Exception\NoSupplierRegisteredException;
use Admin\UseCases\Article\CreateArticle\CreateArticle;
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
        private readonly ConfigurationService $configurationService
    ) {
    }

    #[Route(path: 'articles/create', name: 'admin_article_create', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->configurationService->isSupplierConfigured()) {
            $this->addFlash('error', NoSupplierRegisteredException::MESSAGE);

            return $this->redirectToRoute('admin_configure');
        }
        $form = $this->createForm(CreateArticleType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateArticleInput $article */
            $article = $form->getData();

            if ($article->supplier === null) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException('Supplier expected!');
                // @codeCoverageIgnoreEnd
            }
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
            $zoneStorages = [];
            foreach ($article->zoneStorages as $zoneStorage) {
                $zoneStorages[] = $zoneStorage->toDomain();
            }
            $packaging = $this->getPackaging($article->packaging);

            try {
                $this->useCase->execute(
                    new CreateArticleApiRequest(
                        $article->name,
                        $article->supplier->toDomain(),
                        $packaging,
                        $article->amount,
                        $article->tax->toDomain(),
                        $article->minStock,
                        $zoneStorages,
                        $article->familyLog->toDomain(),
                        $article->quantity ?? 0.0
                    )
                );
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_configure');
            }
            $this->addFlash('success', 'Article created');

            return $this->redirectToRoute('admin_configure');
        }

        return $this->render('@admin/articles/create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @param array{parcel: array{unit: Unit, quantity: string}, subPackage: array{unit: Unit|null, quantity: string|null}, consumeUnit: array{unit: Unit|null, quantity: string|null}} $packaging
     *
     * @return array{array{string, float}, array{string, float}|null, array{string, float}|null}
     */
    private function getPackaging(array $packaging): array
    {
        $parcel = $packaging['parcel'];
        $parcelRequest = [$parcel['unit']->label(), (float) $parcel['quantity']];
        $subPackage = $packaging['subPackage'];
        $subPackageRequest = null;
        if ($subPackage['unit'] !== null && $subPackage['quantity'] !== null) {
            $subPackageRequest = [$subPackage['unit']->label(), (float) $subPackage['quantity']];
        }
        $consumeUnit = $packaging['consumeUnit'];
        $consumeUnitRequest = null;
        if ($consumeUnit['unit'] !== null && $subPackage['quantity'] !== null) {
            $consumeUnitRequest = [$consumeUnit['unit']->label(), (float) $consumeUnit['quantity']];
        }

        return [$parcelRequest, $subPackageRequest, $consumeUnitRequest];
    }
}
