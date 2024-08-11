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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\GetSuppliers;

use Admin\Adapters\Gateway\Pagination\Pagination;
use Admin\Entities\Exception\Supplier\NoSupplierRegisteredException;
use Admin\UseCases\Supplier\GetSuppliers\GetSuppliers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetSuppliersController extends AbstractController
{
    public function __construct(private readonly GetSuppliers $useCase)
    {
    }

    #[Route(
        path: '/suppliers',
        name: 'admin_suppliers_index',
        defaults: ['page' => Pagination::DEFAULT_PAGE, 'itemsPerPage' => Pagination::DEFAULT_ITEMS_PER_PAGE],
        methods: ['GET']
    )]
    public function __invoke(Request $request): Response
    {
        $page = $request->query->getInt('page', Pagination::DEFAULT_PAGE);
        $itemPerPage = $request->query->getInt('itemsPerPage', Pagination::DEFAULT_ITEMS_PER_PAGE);

        try {
            $suppliers = $this->useCase->execute(new GetSuppliersApiRequest($page, $itemPerPage));
        } catch (NoSupplierRegisteredException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('admin_configure');
        }

        $response = new GetSuppliersWebResponse($suppliers);
        $pagination = new Pagination($response->totalItems(), $page, $itemPerPage);

        return $this->render('@admin/suppliers/index.html.twig', [
            'suppliers' => $response->suppliers(),
            'pagination' => $pagination,
        ]);
    }
}
