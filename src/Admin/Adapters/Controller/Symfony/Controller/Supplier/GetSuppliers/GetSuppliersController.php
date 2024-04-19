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

use Admin\Entities\Exception\NoSupplierRegisteredException;
use Admin\UseCases\Supplier\GetSuppliers\GetSuppliers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetSuppliersController extends AbstractController
{
    public function __construct(private readonly GetSuppliers $useCase)
    {
    }

    #[Route(path: '/suppliers', name: 'admin_suppliers_index', methods: ['GET'])]
    public function __invoke(): Response
    {
        try {
            $suppliers = $this->useCase->execute();
        } catch (NoSupplierRegisteredException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('admin_configure');
        }

        $response = new GetSuppliersWebResponse($suppliers);

        return $this->render('@admin/suppliers/index.html.twig', [
            'suppliers' => $response->suppliers(),
        ]);
    }
}
