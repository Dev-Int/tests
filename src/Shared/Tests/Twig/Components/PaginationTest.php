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

use Shared\Twig\Components\Pagination;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

/**
 * @group functionalTest
 *
 * @covers \Shared\Twig\Components\Pagination
 */
final class PaginationTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testPaginateThrowsExceptionWhenRouteIsEmpty(): void
    {
        // Arrange
        $component = $this->mountTwigComponent('Pagination', [
            'page' => 1,
            'itemsPerPage' => 25,
            'totalPages' => 3,
            'route' => '',
        ]);

        // Assert
        self::assertInstanceOf(Pagination::class, $component);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Pagination route must be set');

        // Act
        $component->paginate();
    }

    public function testPaginationRendersWithRoute(): void
    {
        // Arrange && Act
        $component = $this->mountTwigComponent('Pagination', [
            'page' => 2,
            'itemsPerPage' => 25,
            'totalPages' => 5,
            'route' => 'some_route',
        ]);

        // Assert
        self::assertInstanceOf(Pagination::class, $component);
        self::assertSame(2, $component->page);
        self::assertSame(25, $component->itemsPerPage);
        self::assertSame(5, $component->totalPages);
        self::assertSame('some_route', $component->route);
    }
}
