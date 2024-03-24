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

namespace Admin\Adapters\Controller\Symfony\Controller\Unit\GetUnits;

use Admin\UseCases\Unit\GetUnits\GetUnits;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetUnitsController extends AbstractController
{
    public function __construct(private readonly GetUnits $useCase)
    {
    }

    #[Route(path: '/units', name: 'admin_units_index', methods: ['GET'])]
    public function __invoke(): Response
    {
        $units = $this->useCase->execute();

        $response = new GetUnitsWebResponse($units);

        return $this->render('@admin/units/index.html.twig', [
            'units' => $response->units(),
        ]);
    }
}
