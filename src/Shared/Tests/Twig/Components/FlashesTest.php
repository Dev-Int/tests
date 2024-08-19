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

use Shared\Twig\Components\Flashes;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 */
final class FlashesTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testFlashesRendersWithParameters(): void
    {
        // Arrange && Act
        $component = $this->mountTwigComponent('Flashes', [
            'label' => 'success',
            'message' => 'Message de succès',
        ]);

        $renderer = $this->renderTwigComponent('Flashes', [
            'label' => 'success',
            'message' => 'Message de succès',
        ]);

        // Assert
        self::assertInstanceOf(Flashes::class, $component);
        self::assertSame('success', $component->label);
        self::assertSame('Message de succès', $component->message);

        $crawler = $renderer->crawler();
        self::assertSame('flash flash-success', $crawler->filter('div#flashes')->attr('class'));
        self::assertSame('Message de succès', $crawler->filter('div#flashes')->text());
    }
}
