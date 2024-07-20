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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\RenameArticle;

use Admin\Adapters\Form\Type\Article\RenameArticleType;
use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\UseCases\Article\RenameArticle\RenameArticle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class RenameArticleController extends AbstractController
{
    public function __construct(private readonly RenameArticle $useCase)
    {
    }

    #[Route(
        path: 'articles/{article}/rename',
        name: 'admin_articles_rename',
        requirements: ['article' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Article $article): Response
    {
        $form = $this->createForm(
            RenameArticleType::class,
            new RenameArticleApiRequest($article->name(), $article->uuid())
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RenameArticleApiRequest $articleToUpdate */
            $articleToUpdate = $form->getData();

            try {
                $this->useCase->execute($articleToUpdate);
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_articles_index');
            }
            $this->addFlash('success', 'Article updated');

            return $this->redirectToRoute('admin_articles_index');
        }

        return $this->render('@admin/articles/rename.html.twig', [
            'form' => $form,
            'article' => $article,
        ]);
    }
}
