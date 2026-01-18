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

namespace Auth\Tests\EndToEnd\Login;

use Shared\Tests\BasePantherTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class LoginWorkflowTest extends BasePantherTestCase
{
    public function testLoginWithValidCredentials(): void
    {
        // Arrange
        $client = self::createPantherClient();

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $client->request('GET', '/login');
        self::assertSelectorTextContains('h1', $translator->trans('auth.login.titlePage'), 'Navigate to login page');

        $client->submitForm($translator->trans('auth.login.form.submit'), [
            '_email' => 'admin@tests.local',
            '_password' => 'password',
        ]);

        // Assert
        $client->waitForElementToContain('h1', $translator->trans('home.welcome'), 10);
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'), 'Redirected to home');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        // Arrange
        $client = self::createPantherClient();

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $client->request('GET', '/login');
        self::assertSelectorTextContains('h1', $translator->trans('auth.login.titlePage'), 'Navigate to login page');

        $client->submitForm($translator->trans('auth.login.form.submit'), [
            '_email' => 'admin@tests.local',
            '_password' => 'wrongpassword',
        ]);

        // Assert
        $client->waitForVisibility('.flash-error');
        self::assertSelectorTextContains(
            '.flash-error',
            $translator->trans('Invalid credentials.', [], 'security'),
            'Still on login page with error'
        );
    }

    public function testLoginWithInvalidEmail(): void
    {
        // Arrange
        $client = self::createPantherClient();

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $client->request('GET', '/login');
        self::assertSelectorTextContains('h1', $translator->trans('auth.login.titlePage'), 'Navigate to login page');

        $client->submitForm($translator->trans('auth.login.form.submit'), [
            '_email' => 'admin',
            '_password' => 'wrongpassword',
        ]);

        // Assert
        $client->waitForVisibility('.flash-error');
        self::assertSelectorTextContains(
            '.flash-error',
            $translator->trans('Invalid credentials.', [], 'security'),
            'Still on login page with error'
        );
    }
}
