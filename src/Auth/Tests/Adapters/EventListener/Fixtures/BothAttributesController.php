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
use Auth\Contracts\Attribute\RequireRole;

#[RequireAuthenticated]
#[RequireRole(role: 'ROLE_ADMIN')]
final class BothAttributesController
{
    public function __invoke(): void
    {
    }
}
