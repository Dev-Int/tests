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

namespace Shared\Tests\Twig\Components;

use Inventory\Tests\Story\InventoryStory;
use Shared\Twig\Components\Navigation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @group functionalTest
 *
 * @covers \Shared\Twig\Components\Navigation
 */
final class NavigationTest extends KernelTestCase
{
    use Factories;
    use InteractsWithTwigComponents;
    use ResetDatabase;

    public function testNavigationRendersInventoryLinkEnabledWhenConfigured(): void
    {
        // Arrange - Load full config with articles
        InventoryStory::load();

        // Act
        $component = $this->mountTwigComponent('Navigation');
        $renderer = $this->renderTwigComponent('Navigation');

        // Assert
        self::assertInstanceOf(Navigation::class, $component);
        self::assertTrue($component->isApplicationReady());

        $crawler = $renderer->crawler();
        $inventoryLink = $crawler->filter('a[href="/inventories/"]');
        self::assertCount(1, $inventoryLink);
        self::assertNull($inventoryLink->attr('aria-disabled'));
    }

    public function testNavigationRendersInventoryLinkDisabledWhenNotConfigured(): void
    {
        // Arrange - Empty database = not configured

        // Act
        $component = $this->mountTwigComponent('Navigation');
        $renderer = $this->renderTwigComponent('Navigation');

        // Assert
        self::assertInstanceOf(Navigation::class, $component);
        self::assertFalse($component->isApplicationReady());

        $crawler = $renderer->crawler();
        $disabledLink = $crawler->filter('a[aria-disabled="true"]');
        self::assertCount(1, $disabledLink);
        self::assertStringContainsString('Inventaire', $disabledLink->text());
    }
}
