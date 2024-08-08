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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\ChangeArticleFinancialInformation;

use Admin\Adapters\Form\Type\Article\ChangeFinancialInformationType;
use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Entities\Exception\ArticleNotFoundException;
use Admin\UseCases\Article\ChangeFinancialInformation\ChangeArticleFinancialInformation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ChangeArticleFinancialInformationController extends AbstractController
{
    public function __construct(private readonly ChangeArticleFinancialInformation $useCase)
    {
    }

    #[Route(
        path: 'articles/{article}/change-financial-information',
        name: 'admin_articles_change_financial-information',
        requirements: ['article' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: [Request::METHOD_GET, Request::METHOD_POST],
    )]
    public function __invoke(Request $request, Article $article): Response
    {
        $form = $this->createForm(
            ChangeFinancialInformationType::class,
            new ChangeArticleFinancialInformationInput(
                $article->amount(),
                $article->tax(),
                $article->uuid()
            ),
            [
                'action' => $this->generateUrl(
                    'admin_articles_change_financial-information',
                    ['article' => $article->uuid()]
                ),
                'attr' => ['data-turbo-frame' => '_top'],
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChangeArticleFinancialInformationInput $articleToUpdate */
            $articleToUpdate = $form->getData();

            try {
                $this->useCase->execute(new ChangeArticleFinancialInformationApiRequest(
                    amount: $articleToUpdate->amount,
                    tax: $articleToUpdate->tax->toDomain(),
                    uuid: $articleToUpdate->uuid
                ));
                // @codeCoverageIgnoreStart
            } catch (ArticleNotFoundException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('@admin/articles/change-financial-information.html.twig', [
                    'form' => $form,
                    'article' => $article,
                ]);
                // @codeCoverageIgnoreEnd
            }
            $this->addFlash('success', 'Article updated');

            return $this->redirectToRoute('admin_articles_index');
        }

        return $this->render('@admin/articles/change-financial-information.html.twig', [
            'form' => $form,
            'article' => $article,
        ]);
    }
}
