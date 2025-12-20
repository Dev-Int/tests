<?php

namespace {BC}\Tests\UseCases\{Entity}\{Action}{Entity};

use {BC}\Entities\{Entity}\{Entity}Repository;
use {BC}\Tests\DataBuilder\{Entity}DataBuilder;
use {BC}\UseCases\{Entity}\{Action}{Entity}\{Action}{Entity};
use {BC}\UseCases\{Entity}\{Action}{Entity}\{Action}{Entity}Request;
use PHPUnit\Framework\TestCase;

final class {Action}{Entity}Test extends TestCase
{
    public function test{Action}{Entity}(): void
    {
        // Arrange
        $entity = (new {Entity}DataBuilder())->create('Initial')->build();

        $repository = $this->createMock({Entity}Repository::class);
        $useCase = new {Action}{Entity}($repository);
        $request = $this->createMock({Action}{Entity}Request::class);

        // Assert
        $request->expects($this->once())->method('entity')->willReturn($entity);

        $repository->expects($this->once())
            ->method('getByUuid')
            ->with($entity->uuid())
            ->willReturn($entity);

        $repository->expects($this->once())
            ->method('save')
            ->with($entity);

        // Act
        $useCase->execute($request);

        // Assert
        self::assertSame('NewValue', $entity->field()->toString());
    }
}
