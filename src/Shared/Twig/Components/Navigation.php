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

namespace Shared\Twig\Components;

use Shared\Contracts\ApplicationReadinessProvider;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final readonly class Navigation
{
    public function __construct(
        private ApplicationReadinessProvider $applicationReadinessProvider,
    ) {
    }

    public function isApplicationReady(): bool
    {
        return $this->applicationReadinessProvider->isApplicationReady();
    }
}
