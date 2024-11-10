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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\ChangeArticleStorageInformation;

use Admin\Adapters\Form\Type\Article\ChangeStorageInformationType;
use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Adapters\Gateway\ORM\Entity\ReadModel\Packaging;
use Admin\Adapters\Gateway\ORM\Entity\ReadModel\Storage;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Entities\Exception\Article\ArticleNotFoundException;
use Admin\Entities\Unit\Unit as UnitDomain;
use Admin\UseCases\Article\ChangeStorageInformation\ChangeArticleStorageInformation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class ChangeArticleStorageInformationController extends AbstractController
{
    public function __construct(
        private readonly ChangeArticleStorageInformation $useCase,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(
        path: 'articles/{article}/change-article-storage-information',
        name: 'admin_articles_change-storage-information',
        requirements: ['article' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Article $article): Response
    {
        $form = $this->createForm(
            ChangeStorageInformationType::class,
            new ChangeArticleStorageInformationInput(
                packaging: new Packaging(
                    new Storage(
                        $article->packaging()->parcelUnit(),
                        $article->packaging()->parcelQuantity()
                    ),
                    new Storage(
                        $article->packaging()->subPackageUnit(),
                        $article->packaging()->subPackageQuantity()
                    ),
                    new Storage(
                        $article->packaging()->consumeUnitUnit(),
                        $article->packaging()->consumeUnitQuantity()
                    ),
                ),
                minStock: $article->minStock(),
                uuid: $article->uuid()
            ),
            [
                'action' => $this->generateUrl(
                    'admin_articles_change-storage-information',
                    ['article' => $article->uuid()]
                ),
                'attr' => ['data-turbo-frame' => '_top'],
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChangeArticleStorageInformationInput $articleToUpdate */
            $articleToUpdate = $form->getData();

            $packaging = $this->getPackagingDomain($articleToUpdate->packaging);

            try {
                $this->useCase->execute(new ChangeArticleStorageInformationApiRequest(
                    packaging: $packaging,
                    minStock: $articleToUpdate->minStock,
                    uuid: $article->uuid()
                ));
                // @codeCoverageIgnoreStart
            } catch (ArticleNotFoundException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('@admin/articles/change-storage-information.html.twig', [
                    'form' => $form,
                    'article' => $article,
                ]);
                // @codeCoverageIgnoreEnd
            }
            $this->addFlash('success', $this->translator->trans('admin.article.changeStorageInformation.success'));

            return $this->redirectToRoute('admin_articles_index');
        }

        return $this->render('@admin/articles/change-storage-information.html.twig', [
            'form' => $form,
            'article' => $article,
        ]);
    }

    /**
     * @return array{array{UnitDomain, float}, array{UnitDomain, float}|null, array{UnitDomain, float}|null}
     */
    private function getPackagingDomain(Packaging $packaging): array
    {
        $parcel = $packaging->parcel;
        if (!$parcel?->unit instanceof Unit || $parcel->quantity === null) {
            // @codeCoverageIgnoreStart
            throw new \InvalidArgumentException('parcel should have unit and quantity');
            // @codeCoverageIgnoreEnd
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
