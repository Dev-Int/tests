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

namespace Auth\Adapters\Controller\Symfony\Controller\Login;

use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Logout route intercepted by Symfony Security firewall.
 *
 * This controller is never executed - the security firewall intercepts
 * the request and handles the logout process.
 */
#[AsController]
final class LogoutController
{
    public const string ROUTE_NAME = 'auth_logout';

    #[Route(path: '/logout', name: self::ROUTE_NAME, methods: ['GET'])]
    public function __invoke(): never
    {
        // This method is never executed.
        // Symfony Security intercepts this route and handles logout.
        throw new \LogicException('This method should never be reached.');
    }
}
