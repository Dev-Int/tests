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

namespace Auth\Tests\Adapters\EventListener\Fixtures;

use Auth\Contracts\Attribute\RequireAuthenticated;

#[RequireAuthenticated(redirectRoute: 'custom_login', flashMessage: 'Custom login message')]
final class CustomAuthAttributeController
{
    public function __invoke(): void
    {
    }
}
