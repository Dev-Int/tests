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

namespace Shared\Tests\Twig\Components\Icon;

use Shared\Twig\Components\Icon\Check;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 */
final class CheckTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testIconRender(): void
    {
        // Arrange && Act
        $component = $this->mountTwigComponent('Icon:Check');
        $renderer = $this->renderTwigComponent('Icon:Check');

        // Assert
        self::assertInstanceOf(Check::class, $component);

        $crawler = $renderer->crawler();
        self::assertSame('fa-solid fa-square-check', $crawler->filter('i')->attr('class'));
    }
}
