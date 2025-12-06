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

use Shared\Twig\Components\Button\CreateEntity;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 */
final class CreateEntityTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testButtonRendersWithParameters(): void
    {
        // Arrange && Act
        $component = $this->mountTwigComponent('Button:CreateEntity', [
            'label' => 'Créer une unité',
        ]);

        $rendered = $this->renderTwigComponent('Button:CreateEntity', [
            'label' => 'Créer une unité',
        ]);

        // Assert
        self::assertInstanceOf(CreateEntity::class, $component);
        self::assertSame('Créer une unité', $component->label);

        $crawler = $rendered->crawler();
        self::assertSame('Créer une unité', $crawler->filter('a')->text());
        self::assertSame('button', $crawler->filter('a')->attr('role'));

        self::assertSame('fa-solid fa-plus', $crawler->filter('i')->attr('class'));
    }
}
