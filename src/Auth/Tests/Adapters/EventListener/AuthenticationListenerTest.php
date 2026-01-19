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

namespace Auth\Tests\Adapters\EventListener;

use Auth\Adapters\EventListener\AuthenticationListener;
use Auth\Adapters\Service\AttributeResolver;
use Auth\Contracts\CurrentUserProvider;
use Auth\Tests\Adapters\EventListener\Fixtures\BothAttributesController;
use Auth\Tests\Adapters\EventListener\Fixtures\CustomAuthAttributeController;
use Auth\Tests\Adapters\EventListener\Fixtures\NoAttributeController;
use Auth\Tests\Adapters\EventListener\Fixtures\RequireAuthenticatedController;
use Auth\Tests\Adapters\EventListener\Fixtures\RequireRoleController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unitTest
 *
 * @covers \Auth\Adapters\EventListener\AuthenticationListener
 */
final class AuthenticationListenerTest extends TestCase
{
    public function testRedirectsToLoginWhenNotAuthenticated(): void
    {
        // Arrange
        $userProvider = $this->createMock(CurrentUserProvider::class);
        $userProvider->method('isAuthenticated')->willReturn(false);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with('auth_login')
            ->willReturn('/login')
        ;

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('trans')
            ->with('auth.login_required', [], 'auth')
            ->willReturn('Veuillez vous connecter pour accéder à cette page.')
        ;

        $listener = new AuthenticationListener($userProvider, $urlGenerator, $translator, new AttributeResolver());
        $controller = new RequireAuthenticatedController();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ControllerArgumentsEvent(
            $kernel,
            $controller,
            [],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Act
        $listener($event);

        // Assert
        $newController = $event->getController();
        $response = $newController();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());

        $flashes = $session->getFlashBag()->get('warning');
        self::assertCount(1, $flashes);
        self::assertSame('Veuillez vous connecter pour accéder à cette page.', $flashes[0]);
    }

    public function testAllowsAccessWhenAuthenticated(): void
    {
        // Arrange
        $userProvider = $this->createMock(CurrentUserProvider::class);
        $userProvider->expects(self::once())->method('isAuthenticated')->willReturn(true);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $listener = new AuthenticationListener($userProvider, $urlGenerator, $translator, new AttributeResolver());
        $originalController = new RequireAuthenticatedController();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ControllerArgumentsEvent(
            $kernel,
            $originalController,
            [],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Act
        $listener($event);

        // Assert
        self::assertSame($originalController, $event->getController(), 'Controller should remain unchanged');
    }

    public function testThrowsAccessDeniedWhenMissingRole(): void
    {
        // Arrange
        $userProvider = $this->createMock(CurrentUserProvider::class);
        $userProvider->method('isAuthenticated')->willReturn(true);
        $userProvider->method('hasRole')->with('ROLE_ADMIN')->willReturn(false);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $listener = new AuthenticationListener($userProvider, $urlGenerator, $translator, new AttributeResolver());
        $controller = new RequireRoleController();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ControllerArgumentsEvent(
            $kernel,
            $controller,
            [],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Assert & Act
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied. Required role: ROLE_ADMIN');

        $listener($event);
    }

    public function testAllowsAccessWhenHasRole(): void
    {
        // Arrange
        $userProvider = $this->createMock(CurrentUserProvider::class);
        $userProvider->method('isAuthenticated')->willReturn(true);
        $userProvider->method('hasRole')->with('ROLE_ADMIN')->willReturn(true);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $listener = new AuthenticationListener($userProvider, $urlGenerator, $translator, new AttributeResolver());
        $originalController = new RequireRoleController();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ControllerArgumentsEvent(
            $kernel,
            $originalController,
            [],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Act
        $listener($event);

        // Assert
        self::assertSame($originalController, $event->getController(), 'Controller should remain unchanged');
    }

    public function testIgnoresControllersWithoutAttributes(): void
    {
        // Arrange
        $userProvider = $this->createMock(CurrentUserProvider::class);
        $userProvider->expects(self::never())->method('isAuthenticated');
        $userProvider->expects(self::never())->method('hasRole');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $listener = new AuthenticationListener($userProvider, $urlGenerator, $translator, new AttributeResolver());
        $originalController = new NoAttributeController();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ControllerArgumentsEvent(
            $kernel,
            $originalController,
            [],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Act
        $listener($event);

        // Assert
        self::assertSame($originalController, $event->getController(), 'Controller should remain unchanged');
    }

    public function testUsesCustomRedirectRouteAndMessage(): void
    {
        // Arrange
        $userProvider = $this->createMock(CurrentUserProvider::class);
        $userProvider->method('isAuthenticated')->willReturn(false);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with('custom_login')
            ->willReturn('/custom/login')
        ;

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('trans')
            ->with('Custom login message', [], 'auth')
            ->willReturn('Custom login message')
        ;

        $listener = new AuthenticationListener($userProvider, $urlGenerator, $translator, new AttributeResolver());
        $controller = new CustomAuthAttributeController();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ControllerArgumentsEvent(
            $kernel,
            $controller,
            [],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Act
        $listener($event);

        // Assert
        $newController = $event->getController();
        $response = $newController();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/custom/login', $response->getTargetUrl());

        $flashes = $session->getFlashBag()->get('warning');
        self::assertSame('Custom login message', $flashes[0]);
    }

    public function testChecksAuthenticationBeforeRole(): void
    {
        // Arrange - User not authenticated with both attributes
        $userProvider = $this->createMock(CurrentUserProvider::class);
        $userProvider->expects(self::once())->method('isAuthenticated')->willReturn(false);
        // hasRole should never be called if not authenticated
        $userProvider->expects(self::never())->method('hasRole');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with('auth_login')
            ->willReturn('/login')
        ;

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Veuillez vous connecter pour accéder à cette page.');

        $listener = new AuthenticationListener($userProvider, $urlGenerator, $translator, new AttributeResolver());
        $controller = new BothAttributesController();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ControllerArgumentsEvent(
            $kernel,
            $controller,
            [],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Act
        $listener($event);

        // Assert
        $newController = $event->getController();
        $response = $newController();
        self::assertInstanceOf(RedirectResponse::class, $response, 'Should redirect to login page, not throw AccessDeniedException');
    }

    public function testRedirectsWithoutFlashWhenSessionDoesNotSupportFlashBag(): void
    {
        // Arrange
        $userProvider = $this->createMock(CurrentUserProvider::class);
        $userProvider->method('isAuthenticated')->willReturn(false);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with('auth_login')
            ->willReturn('/login')
        ;

        $translator = $this->createMock(TranslatorInterface::class);
        // trans() should never be called when session doesn't support FlashBag
        $translator->expects(self::never())->method('trans');

        $listener = new AuthenticationListener($userProvider, $urlGenerator, $translator, new AttributeResolver());
        $controller = new RequireAuthenticatedController();

        $request = new Request();
        // Mock session sans FlashBagAwareSessionInterface
        $session = $this->createMock(SessionInterface::class);
        $request->setSession($session);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ControllerArgumentsEvent(
            $kernel,
            $controller,
            [],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Act
        $listener($event);

        // Assert - Redirect fonctionne, pas de flash (mais pas d'erreur non plus)
        $newController = $event->getController();
        $response = $newController();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());
    }
}
