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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\GetArticles;

use Admin\Adapters\Gateway\Pagination\Pagination;
use Admin\Entities\Exception\Article\NoArticleRegisteredException;
use Admin\UseCases\Article\GetArticles\GetArticles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetArticlesController extends AbstractController
{
    public function __construct(private readonly GetArticles $useCase)
    {
    }

    #[Route(
        path: 'articles',
        name: 'admin_articles_index',
        defaults: ['page' => Pagination::DEFAULT_PAGE, 'itemsPerPage' => Pagination::DEFAULT_ITEMS_PER_PAGE],
        methods: ['GET']
    )]
    public function __invoke(Request $request): Response
    {
        $page = $request->query->getInt('page', Pagination::DEFAULT_PAGE);
        $itemPerPage = $request->query->getInt('itemsPerPage', Pagination::DEFAULT_ITEMS_PER_PAGE);

        try {
            $articles = $this->useCase->execute(new GetArticlesApiRequest($page, $itemPerPage));
        } catch (NoArticleRegisteredException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('admin_configure');
        }

        $response = new GetArticlesWebResponse($articles);
        $pagination = new Pagination($response->totalItems(), $page, $itemPerPage);

        return $this->render('@admin/articles/index.html.twig', [
            'articles' => $response->articles(),
            'pagination' => $pagination,
        ]);
    }
}
