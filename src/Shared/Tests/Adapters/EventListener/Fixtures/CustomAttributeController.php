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

namespace Shared\Tests\Adapters\EventListener\Fixtures;

use Shared\Adapters\Attribute\RequireApplicationReady;

#[RequireApplicationReady(redirectRoute: 'custom_route', flashMessage: 'Custom message')]
final class CustomAttributeController
{
    public function __invoke(): void
    {
    }
}
