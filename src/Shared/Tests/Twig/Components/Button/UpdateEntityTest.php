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

use Shared\Twig\Components\Button\UpdateEntity;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 */
final class UpdateEntityTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testButtonRendersWithParameters(): void
    {
        // Arrange && Act
        $component = $this->mountTwigComponent('Button:UpdateEntity', [
            'label' => 'Corriger l\'entité',
        ]);

        $rendered = $this->renderTwigComponent('Button:UpdateEntity', [
            'label' => 'Corriger l\'entité',
        ]);

        // Assert
        self::assertInstanceOf(UpdateEntity::class, $component);
        self::assertSame('Corriger l\'entité', $component->label);

        $crawler = $rendered->crawler();
        self::assertSame('Corriger l\'entité', $crawler->filter('a')->text());
        self::assertSame('button', $crawler->filter('a')->attr('role'));

        self::assertSame('fa-solid fa-pen-to-square', $crawler->filter('i')->attr('class'));
    }
}
