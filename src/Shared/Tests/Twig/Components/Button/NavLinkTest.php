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

namespace Shared\Tests\Twig\Components\Button;

use Shared\Twig\Components\Button\NavLink;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 *
 * @covers \Shared\Twig\Components\Button\NavLink
 */
final class NavLinkTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testNavLinkRendersActiveLink(): void
    {
        // Arrange & Act
        $component = $this->mountTwigComponent('Button:NavLink', [
            'href' => '/test-url',
            'label' => 'Test Label',
        ]);
        $renderer = $this->renderTwigComponent('Button:NavLink', [
            'href' => '/test-url',
            'label' => 'Test Label',
        ]);

        // Assert
        self::assertInstanceOf(NavLink::class, $component);
        self::assertSame('/test-url', $component->href);
        self::assertSame('Test Label', $component->label);
        self::assertFalse($component->disabled);

        $crawler = $renderer->crawler();
        $link = $crawler->filter('a');
        self::assertCount(1, $link);
        self::assertSame('/test-url', $link->attr('href'));
        self::assertSame('button', $link->attr('role'));
        self::assertNull($link->attr('aria-disabled'));
        self::assertStringContainsString('Test Label', $link->text());
    }

    public function testNavLinkRendersDisabledLink(): void
    {
        // Arrange & Act
        $component = $this->mountTwigComponent('Button:NavLink', [
            'href' => '/test-url',
            'label' => 'Disabled Link',
            'disabled' => true,
        ]);
        $renderer = $this->renderTwigComponent('Button:NavLink', [
            'href' => '/test-url',
            'label' => 'Disabled Link',
            'disabled' => true,
        ]);

        // Assert
        self::assertInstanceOf(NavLink::class, $component);
        self::assertTrue($component->disabled);

        $crawler = $renderer->crawler();
        $link = $crawler->filter('a');
        self::assertCount(1, $link);
        self::assertSame('#', $link->attr('href'));
        self::assertSame('true', $link->attr('aria-disabled'));
        self::assertStringContainsString('secondary', (string) $link->attr('class'));
        self::assertStringContainsString('Disabled Link', $link->text());
    }
}
