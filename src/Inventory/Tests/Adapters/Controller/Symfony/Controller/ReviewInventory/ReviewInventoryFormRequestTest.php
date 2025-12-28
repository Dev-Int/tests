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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\ReviewInventory;

use Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\ReviewInventoryFormRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\ReviewInventoryFormRequest
 */
final class ReviewInventoryFormRequestTest extends TestCase
{
    public function testItemIdentifiersReturnsCorrectFormat(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $articleUuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $zoneStorageUuid = 'f1e2d3c4-b5a6-0987-dcba-0987654321fe';
        $selectedItems = [\sprintf('%s_%s', $articleUuid, $zoneStorageUuid)];

        $request = new ReviewInventoryFormRequest($inventoryUuid, $selectedItems);

        // Act
        $result = $request->itemIdentifiers();

        // Assert
        self::assertCount(1, $result);
        self::assertSame($articleUuid, $result[0]['articleUuid']->toString());
        self::assertSame($zoneStorageUuid, $result[0]['zoneStorageUuid']->toString());
    }

    public function testItemIdentifiersThrowsExceptionForMalformedIdentifierWithoutSeparator(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $malformedIdentifier = 'missing-separator-uuid';
        $request = new ReviewInventoryFormRequest($inventoryUuid, [$malformedIdentifier]);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid item identifier format: "missing-separator-uuid"');

        // Act
        $request->itemIdentifiers();
    }

    public function testItemIdentifiersThrowsExceptionForIdentifierWithTooManyParts(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $malformedIdentifier = 'part1_part2_part3';
        $request = new ReviewInventoryFormRequest($inventoryUuid, [$malformedIdentifier]);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid item identifier format: "part1_part2_part3"');

        // Act
        $request->itemIdentifiers();
    }

    public function testItemIdentifiersThrowsExceptionForEmptyIdentifier(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $request = new ReviewInventoryFormRequest($inventoryUuid, ['']);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid item identifier format: ""');

        // Act
        $request->itemIdentifiers();
    }

    public function testInventoryUuidReturnsProvidedUuid(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $request = new ReviewInventoryFormRequest($inventoryUuid, []);

        // Act & Assert
        self::assertSame($inventoryUuid, $request->inventoryUuid());
    }
}
