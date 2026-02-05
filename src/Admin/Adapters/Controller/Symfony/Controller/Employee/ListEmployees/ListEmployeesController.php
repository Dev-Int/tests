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
use Admin\Entities\Repository\EmployeeRepository;
use Admin\UseCases\Employee\GetActiveEmployees\GetActiveEmployees;
use Admin\UseCases\Employee\GetActiveEmployees\GetActiveEmployeesApiRequest;
use Auth\Contracts\Attribute\RequireRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RequireRole('ROLE_ADMIN')]
final class ListEmployeesController extends AbstractController
{
    public const string ROUTE_NAME = 'admin_employees_index';

    public function __construct(
        private readonly GetActiveEmployees $useCase,
        private readonly EmployeeRepository $repository,
    ) {
    }

    #[Route(path: 'employees', name: self::ROUTE_NAME, methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', '1'));
        $itemsPerPage = max(1, (int) $request->query->get('itemsPerPage', '20'));

        try {
            $response = $this->useCase->execute(
                new GetActiveEmployeesApiRequest($page, $itemsPerPage)
            );

            $totalCount = $this->repository->getActiveEmployeesCount();
            $totalPages = (int) ceil($totalCount / $itemsPerPage);

            return $this->render('@admin/employees/index.html.twig', [
                'employees' => $response->employees(),
                'pagination' => [
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'totalPages' => $totalPages,
                ],
            ]);
        } catch (NoEmployeeRegistered) {
            return $this->render('@admin/employees/index.html.twig', [
                'employees' => [],
                'pagination' => [
                    'page' => 1,
                    'itemsPerPage' => $itemsPerPage,
                    'totalPages' => 0,
                ],
            ]);
        }
    }
}
