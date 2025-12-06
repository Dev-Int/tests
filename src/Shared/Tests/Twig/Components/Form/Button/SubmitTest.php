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

use Shared\Twig\Components\Form\Button\Submit;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 */
final class SubmitTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testButtonCancelRendersWithParameters(): void
    {
        // Arrange && Act
        $component = $this->mountTwigComponent('Form:Button:Submit', [
            'label' => 'Créer',
        ]);

        $rendered = $this->renderTwigComponent('Form:Button:Submit', [
            'label' => 'Créer',
        ]);

        // Assert
        self::assertInstanceOf(Submit::class, $component);
        self::assertSame('Créer', $component->label);

        $crawler = $rendered->crawler();
        self::assertSame('Créer', $crawler->filter('button')->text());
        self::assertSame('submit', $crawler->filter('button')->attr('type'));
        self::assertSame('', $crawler->filter('button')->attr('formnovalidate'));
    }
}
