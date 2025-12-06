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

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @codeCoverageIgnore
 */
#[AsTwigComponent]
final class Flashes
{
    public string $label;
    public string $message;
}
