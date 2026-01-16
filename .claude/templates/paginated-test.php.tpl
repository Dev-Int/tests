<?php

declare(strict_types=1);

namespace {BC}\Tests\UseCases\{Entity}\Get{EntityPlural};

use {BC}\Entities\{Entity}\{Entity}Collection;
use {BC}\Entities\Repository\{Entity}Repository;
use {BC}\Tests\DataBuilder\{Entity}DataBuilder;
use {BC}\UseCases\{Entity}\Get{EntityPlural}\Get{EntityPlural};
use {BC}\UseCases\{Entity}\Get{EntityPlural}\Get{EntityPlural}Request;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class Get{EntityPlural}Test extends TestCase
{
    public function testGet{EntityPlural}WithPaginationSucceeds(): void
    {
        // Arrange
        $repository = $this->createMock({Entity}Repository::class);
        $request = $this->createMock(Get{EntityPlural}Request::class);
        $useCase = new Get{EntityPlural}($repository);

        // Collection avec totalItems pour pagination
        // totalItems = nombre total en BDD (pas taille page)
        $collection = new {Entity}Collection(totalItems: 2);
        $collection->add((new {Entity}DataBuilder())->build());
        $collection->add((new {Entity}DataBuilder())->build());

        $request->expects(self::once())->method('page')->willReturn(1);
        $request->expects(self::once())->method('itemsPerPage')->willReturn(10);

        $repository->expects(self::once())
            ->method('getAll{EntityPlural}Paginated')
            ->with(1, 10)
            ->willReturn($collection);

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertCount(2, $response->collection);
    }
}