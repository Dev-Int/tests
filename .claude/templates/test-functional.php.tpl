<?php

namespace {BC}\Tests\Adapters\Controller\Symfony\Controller\{Entity}{Action};

use {BC}\Tests\Factory\{Entity}Factory;
use Shared\Tests\BaseFunctionalTestCase;
use Zenstruck\Foundry\Test\Factories;

final class {Entity}{Action}ControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    public function testSuccess(): void
    {
        // Arrange
        {Entity}Factory::createMany(5, ['name' => 'Test']);

        // Act
        $this->client->request('GET', '/path/to/resource');

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.selector');
    }
}
