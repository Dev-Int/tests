<?php

namespace {BC}\Tests\EndToEnd\{Entity};

use {BC}\Tests\Factory\{Entity}Factory;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Zenstruck\Foundry\Test\Factories;

final class {WorkflowName}Test extends BasePantherTestCase
{
    use Factories;

    public function test{WorkflowName}(): void
    {
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);
        $translator = self::getContainer()->get('translator');

        // Arrange
        $this->createMinimalConfiguration();
        $this->flushAndClearEntityManager();

        // CRITICAL: Start from root
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        // Navigate by clicking
        $client->clickLink($translator->trans('link.text'));
        $client->wait(1);

        // Act & Assert
        // ...
    }
}
