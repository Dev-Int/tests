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

namespace Auth\Tests\Adapters\Controller\Symfony\Controller\ResetPassword;

use Auth\Adapters\Gateway\ORM\Entity\User;
use Auth\Adapters\Gateway\ORM\Repository\DoctrinePasswordResetTokenRepository;
use Auth\Entities\ResetPassword;
use Auth\Tests\Factory\UserFactory;
use Shared\Entities\ResourceUuid;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @group functionalTest
 *
 * @covers \Auth\Adapters\Controller\Symfony\Controller\ResetPassword\PasswordResetController
 */
final class PasswordResetControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use ResetDatabase;

    private const string TEST_PASSWORD = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->logoutUser();
    }

    public function testValidTokenShowsForm(): void
    {
        // Arrange
        $token = 'valid-token-aabbccddee112233';

        $user = UserFactory::createOne();
        $this->createToken($token, $user->_real());

        // Act
        $this->client->request(Request::METHOD_GET, '/password-reset/' . $token);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testUnknownTokenRedirectsToLogin(): void
    {
        // Arrange && Act
        $this->client->request(Request::METHOD_GET, '/password-reset/unknown-token-xyz');

        // Assert
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorExists('.flash-error');
    }

    public function testExpiredTokenRedirectsToLogin(): void
    {
        // Arrange
        $token = 'expired-token-aabbccddee112233';

        $user = UserFactory::createOne();
        $this->createToken($token, $user->_real(), expiresAt: new \DateTimeImmutable('-1 hour'));

        // Act
        $this->client->request(Request::METHOD_GET, '/password-reset/' . $token);

        // Assert
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorExists('.flash-error');
    }

    public function testUsedTokenRedirectsToLogin(): void
    {
        // Arrange
        $token = 'used-token-aabbccddee112233';

        $user = UserFactory::createOne();
        $this->createToken($token, $user->_real(), usedAt: new \DateTimeImmutable());

        // Act
        $this->client->request(Request::METHOD_GET, '/password-reset/' . $token);

        // Assert
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorExists('.flash-error');
    }

    public function testDisabledUserTokenRedirectsToLogin(): void
    {
        // Arrange
        $token = 'disabled-user-token-aabbccddee112233';

        $user = UserFactory::createOne(['disabledAt' => new \DateTimeImmutable()]);
        $this->createToken($token, $user->_real());

        // Act
        $this->client->request(Request::METHOD_GET, '/password-reset/' . $token);

        // Assert
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorExists('.flash-error');
    }

    public function testValidSubmitResetsPasswordAndRedirectsToLogin(): void
    {
        // Arrange
        $token = 'valid-submit-token-aabbccddee112233';
        $hashedPassword = $this->hashPassword(self::TEST_PASSWORD);

        $user = UserFactory::createOne(['password' => $hashedPassword]);
        $this->createToken($token, $user->_real());

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $this->client->request(Request::METHOD_GET, '/password-reset/' . $token);
        $this->client->submitForm($translator->trans('auth.reset_password.form.submit'), [
            'password_reset[password][first]' => 'NewStr0ngPassword!',
            'password_reset[password][second]' => 'NewStr0ngPassword!',
        ]);

        // Assert
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorExists('.flash-success');
    }

    public function testInvalidPasswordFormStaysOnPage(): void
    {
        // Arrange
        $token = 'form-error-token-aabbccddee112233';

        $user = UserFactory::createOne();
        $this->createToken($token, $user->_real());

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $this->client->request(Request::METHOD_GET, '/password-reset/' . $token);
        $this->client->submitForm($translator->trans('auth.reset_password.form.submit'), [
            'password_reset[password][first]' => 'short',
            'password_reset[password][second]' => 'short',
        ]);

        // Assert
        self::assertResponseIsUnprocessable();
        self::assertSelectorExists('form');
    }

    private function createToken(
        string $token,
        User $user,
        ?\DateTimeImmutable $expiresAt = null,
        ?\DateTimeImmutable $usedAt = null,
    ): void {
        /** @var DoctrinePasswordResetTokenRepository $repository */
        $repository = self::getContainer()->get(DoctrinePasswordResetTokenRepository::class);

        $resetPassword = new ResetPassword(
            id: ResourceUuid::generate(),
            user: $user->toDomain(),
            token: $token,
            expiresAt: $expiresAt ?? new \DateTimeImmutable('+1 hour'),
            usedAt: $usedAt,
        );

        $repository->create($resetPassword);
    }

    private function hashPassword(string $plainPassword): string
    {
        /** @var PasswordHasherFactoryInterface $hasherFactory */
        $hasherFactory = self::getContainer()->get(PasswordHasherFactoryInterface::class);
        $hasher = $hasherFactory->getPasswordHasher(User::class);

        return $hasher->hash($plainPassword);
    }
}
