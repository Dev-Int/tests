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

namespace Shared\Adapters\Attribute;

use Shared\Adapters\Exception\ApplicationNotAlreadyConfigured;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final readonly class RequireApplicationReady
{
    public function __construct(
        public string $redirectRoute = 'admin_configure',
        public string $flashMessage = ApplicationNotAlreadyConfigured::MESSAGE,
    ) {
    }
}
