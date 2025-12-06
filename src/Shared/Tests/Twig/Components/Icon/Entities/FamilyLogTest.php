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

use Shared\Twig\Components\Icon\Entities\FamilyLog;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 */
final class FamilyLogTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testIconRender(): void
    {
        // Arrange && Act
        $component = $this->mountTwigComponent('Icon:Entities:FamilyLog');
        $renderer = $this->renderTwigComponent('Icon:Entities:FamilyLog');

        // Assert
        self::assertInstanceOf(FamilyLog::class, $component);

        $crawler = $renderer->crawler();
        self::assertSame('fa-solid fa-tags', $crawler->filter('i')->attr('class'));
    }
}
