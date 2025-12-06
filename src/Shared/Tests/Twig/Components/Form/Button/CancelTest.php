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

namespace Shared\Tests\Twig\Components\Form\Button;

use Shared\Twig\Components\Form\Button\Cancel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 */
final class CancelTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testButtonCancelRendersWithParameters(): void
    {
        // Arrange && Act
        $component = $this->mountTwigComponent('Form:Button:Cancel', [
            'label' => 'Annuler',
            'href' => '/',
        ]);

        $rendered = $this->renderTwigComponent('Form:Button:Cancel', [
            'label' => 'Annuler',
            'href' => '/',
        ]);

        // Assert
        self::assertInstanceOf(Cancel::class, $component);
        self::assertSame('Annuler', $component->label);
        self::assertSame('/', $component->href);

        $crawler = $rendered->crawler();
        self::assertSame('Annuler', $crawler->filter('a')->text());
        self::assertSame('/', $crawler->filter('a')->attr('href'));
        self::assertSame('button', $crawler->filter('a')->attr('role'));
        self::assertSame('secondary', $crawler->filter('a')->attr('class'));
    }
}
