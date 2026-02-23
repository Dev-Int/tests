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

namespace Auth\Adapters\Controller\Symfony\Controller\User\GetUsers;

use Auth\Contracts\Attribute\RequireRole;
use Auth\UseCases\User\GetUsers\GetUsers;
use Shared\Adapters\Gateway\Pagination\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RequireRole(role: 'ROLE_ADMIN')]
final class GetUsersController extends AbstractController
{
    public const string ROUTE_NAME = 'auth_users_index';

    public function __construct(private readonly GetUsers $useCase)
    {
    }

    #[Route(
        path: '/admin/users',
        name: self::ROUTE_NAME,
        defaults: ['page' => Pagination::DEFAULT_PAGE, 'itemsPerPage' => Pagination::DEFAULT_ITEMS_PER_PAGE],
        methods: ['GET']
    )]
    public function __invoke(Request $request): Response
    {
        $page = $request->query->getInt('page', Pagination::DEFAULT_PAGE);
        $itemsPerPage = $request->query->getInt('itemsPerPage', Pagination::DEFAULT_ITEMS_PER_PAGE);

        $users = $this->useCase->execute(new GetUsersApiRequest($page, $itemsPerPage));

        $response = new GetUsersWebResponse($users);
        $pagination = new Pagination($response->totalItems(), $page, $itemsPerPage);

        return $this->render('@auth/user/index.html.twig', [
            'users' => $response->users(),
            'pagination' => $pagination,
        ]);
    }
}
