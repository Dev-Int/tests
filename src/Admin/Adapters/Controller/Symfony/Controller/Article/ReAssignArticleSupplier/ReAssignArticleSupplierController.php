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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\ReAssignArticleSupplier;

use Admin\Adapters\Form\Type\Article\ReAssignSupplierType;
use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\UseCases\Article\ReAssignSupplier\ReAssignArticleSupplier;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ReAssignArticleSupplierController extends AbstractController
{
    public function __construct(private readonly ReAssignArticleSupplier $useCase)
    {
    }

    #[Route(
        path: 'articles/{article}/reassign-supplier',
        name: 'admin_articles_reassign-supplier',
        requirements: ['article' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Article $article): Response
    {
        $zoneStorages = new ArrayCollection();
        foreach ($article->zoneStorages() as $zoneStorage) {
            $zoneStorages->add($zoneStorage);
        }
        $form = $this->createForm(
            ReAssignSupplierType::class,
            new ReAssignArticleSupplierDto(
                $article->supplier(),
                $article->familyLog(),
                $zoneStorages,
                $article->uuid()
            ),
            [
                'action' => $this->generateUrl(
                    'admin_articles_reassign-supplier',
                    ['article' => $article->uuid()]
                ),
                'attr' => ['data-turbo-frame' => '_top'],
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ReAssignArticleSupplierDto $articleToUpdate */
            $articleToUpdate = $form->getData();
            $zoneStoragesRequest = [];
            foreach ($articleToUpdate->zoneStorages as $zoneStorage) {
                $zoneStoragesRequest[] = $zoneStorage->toDomain();
            }

            try {
                $this->useCase->execute(new ReAssignArticleSupplierApiRequest(
                    $articleToUpdate->supplier->toDomain(),
                    $articleToUpdate->familyLog->toDomain(),
                    $zoneStoragesRequest,
                    $articleToUpdate->uuid
                ));
                // @codeCoverageIgnoreStart
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('@admin/articles/reassign-supplier.html.twig', [
                    'form' => $form,
                    'article' => $article,
                ]);
                // @codeCoverageIgnoreEnd
            }
            $this->addFlash('success', 'Article updated');

            return $this->redirectToRoute('admin_articles_index');
        }

        return $this->render('@admin/articles/reassign-supplier.html.twig', [
            'form' => $form,
            'article' => $article,
        ]);
    }
}
