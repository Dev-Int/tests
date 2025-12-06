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

namespace Shared\Tests\Twig\Components\Icon\Entities;

use Shared\Twig\Components\Icon\Entities\Supplier;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 */
final class SupplierTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testIconRender(): void
    {
        // Arrange && Act
        $component = $this->mountTwigComponent('Icon:Entities:Supplier');
        $renderer = $this->renderTwigComponent('Icon:Entities:Supplier');

        // Assert
        self::assertInstanceOf(Supplier::class, $component);

        $crawler = $renderer->crawler();
        self::assertSame('fa-solid fa-truck-ramp-box', $crawler->filter('i')->attr('class'));
    }
}
