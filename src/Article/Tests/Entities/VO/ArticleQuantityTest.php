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

namespace Article\Tests\Entities\VO;

use Article\Entities\Exception\NegativeValueException;
use Article\Entities\VO\ArticleQuantity;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class ArticleQuantityTest extends TestCase
{
    public function testInstantiateArticleQuantityWithFloat(): void
    {
        // Arrange & Act
        $quantity = ArticleQuantity::fromFloat(1.25);

        // Assert
        self::assertSame(1.250, $quantity->toFloat());
        self::assertSame(1250, $quantity->toInt());
    }

    public function testInstantiateArticleQuantityWithNull(): void
    {
        // Arrange & Act
        $quantity = ArticleQuantity::fromFloat(null);

        // Assert
        self::assertSame(0.0, $quantity->toFloat());
        self::assertSame(0, $quantity->toInt());
    }

    public function testArticleQuantityThrowNegativeValueException(): void
    {
        // Assert
        $this->expectException(NegativeValueException::class);
        $this->expectExceptionMessage(NegativeValueException::MESSAGE);

        // Arrange && Act
        ArticleQuantity::fromFloat(-1.25);
    }
}
