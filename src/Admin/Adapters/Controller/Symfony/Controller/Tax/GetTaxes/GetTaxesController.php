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

namespace Admin\Adapters\Controller\Symfony\Controller\Tax\GetTaxes;

use Admin\UseCases\Tax\GetTaxes\GetTaxes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetTaxesController extends AbstractController
{
    public function __construct(private readonly GetTaxes $useCase)
    {
    }

    #[Route(path: '/taxes', name: 'admin_taxes_index', methods: ['GET'])]
    public function __invoke(): Response
    {
        $taxes = $this->useCase->execute();

        $response = new GetTaxesWebResponse($taxes);

        return $this->render('@admin/taxes/index.html.twig', [
            'taxes' => $response->taxes(),
        ]);
    }
}
