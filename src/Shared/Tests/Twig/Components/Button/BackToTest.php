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

use Shared\Twig\Components\Button\BackTo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 */
final class BackToTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testButtonRendersWithParameters(): void
    {
        // Arrange && Act
        $component = $this->mountTwigComponent('Button:BackTo', [
            'label' => 'Retour à l\'accueil',
            'href' => '/',
        ]);

        $rendered = $this->renderTwigComponent('Button:BackTo', [
            'label' => 'Retour à l\'accueil',
            'href' => '/',
        ]);

        // Assert
        self::assertInstanceOf(BackTo::class, $component);
        self::assertSame('Retour à l\'accueil', $component->label);
        self::assertSame('/', $component->href);

        $crawler = $rendered->crawler();
        self::assertSame('Retour à l\'accueil', $crawler->filter('a')->text());
        self::assertSame('/', $crawler->filter('a')->attr('href'));
        self::assertSame('button', $crawler->filter('a')->attr('role'));

        self::assertSame('fa-solid fa-angles-left', $crawler->filter('i')->attr('class'));
    }
}
