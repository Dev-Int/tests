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

namespace Auth\Tests\Adapters\Controller\Symfony\Controller\Login;

use Auth\Adapters\Gateway\ORM\Entity\User;
use Auth\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @group functionalTest
 *
 * @covers \Auth\Adapters\Controller\Symfony\Controller\Login\LoginController
 */
final class LoginControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    private const string LOGIN_URI = '/login';
    private const string TEST_PASSWORD = 'password123';

    public function testLoginPageIsAccessible(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $client->request(Request::METHOD_GET, self::LOGIN_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('auth.login.titlePage'));

        // Verify form elements
        self::assertSelectorExists('input[name="_email"]');
        self::assertSelectorExists('input[name="_password"]');
        self::assertSelectorExists('input[name="_remember_me"]');
        self::assertSelectorExists('input[name="_csrf_token"]');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testLoginWithValidCredentialsRedirectsToHome(): void
    {
        // Arrange
        $client = self::createClient();
        $hashedPassword = $this->hashPassword(self::TEST_PASSWORD);

        UserFactory::createOne([
            'email' => 'user@example.com',
            'password' => $hashedPassword,
        ]);

        // Act
        $this->submitLoginForm($client, 'user@example.com', self::TEST_PASSWORD);

        // Assert
        self::assertResponseRedirects('/');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testLoginWithInvalidCredentialsShowsError(): void
    {
        // Arrange
        $client = self::createClient();
        $hashedPassword = $this->hashPassword(self::TEST_PASSWORD);

        UserFactory::createOne([
            'email' => 'user@example.com',
            'password' => $hashedPassword,
        ]);

        // Act
        $this->submitLoginForm($client, 'user@example.com', 'wrong_password');

        // Assert
        self::assertResponseRedirects(self::LOGIN_URI);
        $client->followRedirect();
        self::assertSelectorExists('.flash-error');
    }

    public function testLoginWithDisabledUserShowsError(): void
    {
        // Arrange
        $client = self::createClient();
        $hashedPassword = $this->hashPassword(self::TEST_PASSWORD);

        UserFactory::createOne([
            'email' => 'disabled@example.com',
            'password' => $hashedPassword,
            'disabledAt' => new \DateTimeImmutable(),
        ]);

        // Act
        $this->submitLoginForm($client, 'disabled@example.com', self::TEST_PASSWORD);

        // Assert
        self::assertResponseRedirects(self::LOGIN_URI);
        $client->followRedirect();
        self::assertSelectorExists('.flash-error');
    }

    public function testLogoutRedirectsToLogin(): void
    {
        // Arrange
        $client = self::createClient();
        $hashedPassword = $this->hashPassword(self::TEST_PASSWORD);

        $user = UserFactory::createOne([
            'email' => 'user@example.com',
            'password' => $hashedPassword,
        ]);

        $client->loginUser($user->_real());

        // Act
        $client->request(Request::METHOD_GET, '/logout');

        // Assert
        self::assertResponseRedirects(self::LOGIN_URI);
    }

    public function testAuthenticatedUserIsRedirectedFromLoginPage(): void
    {
        // Arrange
        $client = self::createClient();
        $hashedPassword = $this->hashPassword(self::TEST_PASSWORD);

        $user = UserFactory::createOne([
            'email' => 'user@example.com',
            'password' => $hashedPassword,
        ]);

        $client->loginUser($user->_real());

        // Act
        $client->request(Request::METHOD_GET, self::LOGIN_URI);

        // Assert
        self::assertResponseRedirects('/');
    }

    public function testRememberMeCheckboxExists(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::LOGIN_URI);

        // Assert
        self::assertResponseIsSuccessful();
        $checkbox = $crawler->filter('input[name="_remember_me"][type="checkbox"]');
        self::assertCount(1, $checkbox, 'Remember me checkbox should exist');
    }

    private function hashPassword(string $plainPassword): string
    {
        /** @var PasswordHasherFactoryInterface $hasherFactory */
        $hasherFactory = self::getContainer()->get(PasswordHasherFactoryInterface::class);
        $hasher = $hasherFactory->getPasswordHasher(User::class);

        return $hasher->hash($plainPassword);
    }

    private function submitLoginForm(
        KernelBrowser $client,
        string $email,
        string $password,
    ): void {
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $client->request(Request::METHOD_GET, self::LOGIN_URI);
        $client->submitForm($translator->trans('auth.login.form.submit'), [
            '_email' => $email,
            '_password' => $password,
        ]);
    }
}
