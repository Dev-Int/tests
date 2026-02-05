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

namespace Admin\Adapters\Controller\Symfony\Controller\Employee\ListEmployees;

use Admin\Entities\Exception\Employee\NoEmployeeRegistered;
use Admin\UseCases\Employee\GetEmployees\GetEmployees;
use Auth\Contracts\Attribute\RequireRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RequireRole('ROLE_ADMIN')]
final class ListEmployeesController extends AbstractController
{
    public const string ROUTE_NAME = 'admin_employees_index';

    public function __construct(
        private readonly GetEmployees $useCase,
    ) {
    }

    #[Route(path: 'employees', name: self::ROUTE_NAME, methods: ['GET'])]
    public function __invoke(): Response
    {
        try {
            $response = $this->useCase->execute();

            return $this->render('@admin/employees/index.html.twig', [
                'employees' => $response->employees(),
            ]);
        } catch (NoEmployeeRegistered) {
            return $this->render('@admin/employees/index.html.twig', [
                'employees' => [],
            ]);
        }
    }
}
