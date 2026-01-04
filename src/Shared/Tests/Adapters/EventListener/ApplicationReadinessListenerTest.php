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

namespace Shared\Tests\Adapters\EventListener;

use PHPUnit\Framework\TestCase;
use Shared\Adapters\EventListener\ApplicationReadinessListener;
use Shared\Adapters\Exception\ApplicationNotReady;
use Shared\Contracts\ApplicationReadinessProvider;
use Shared\Tests\Adapters\EventListener\Fixtures\CustomAttributeController;
use Shared\Tests\Adapters\EventListener\Fixtures\DefaultAttributeController;
use Shared\Tests\Adapters\EventListener\Fixtures\NoAttributeController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group unitTest
 *
 * @covers \Shared\Adapters\EventListener\ApplicationReadinessListener
 */
final class ApplicationReadinessListenerTest extends TestCase
{
    public function testRedirectsWhenApplicationNotReady(): void
    {
        // Arrange
        $provider = $this->createMock(ApplicationReadinessProvider::class);
        $provider->method('isApplicationReady')->willReturn(false);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $listener = new ApplicationReadinessListener($provider, $urlGenerator);
        $controller = new DefaultAttributeController();

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

        // Assert
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with('admin_configure')
            ->willReturn('/admin/configure')
        ;

        // Act
        $listener($event);

        // Assert
        $newController = $event->getController();
        $response = $newController();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/admin/configure', $response->getTargetUrl());

        $flashes = $session->getFlashBag()->get('error');
        self::assertCount(1, $flashes);
        self::assertSame(ApplicationNotReady::MESSAGE, $flashes[0]);
    }

    public function testAllowsAccessWhenApplicationReady(): void
    {
        // Arrange
        $provider = $this->createMock(ApplicationReadinessProvider::class);
        $provider->expects(self::once())->method('isApplicationReady')->willReturn(true);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $listener = new ApplicationReadinessListener($provider, $urlGenerator);
        $originalController = new DefaultAttributeController();

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

        // Assert - Controller should remain unchanged
        self::assertSame($originalController, $event->getController());
    }

    public function testIgnoresControllersWithoutAttribute(): void
    {
        // Arrange
        $provider = $this->createMock(ApplicationReadinessProvider::class);
        $provider->expects(self::never())->method('isApplicationReady');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $listener = new ApplicationReadinessListener($provider, $urlGenerator);
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

        // Assert - Controller should remain unchanged
        self::assertSame($originalController, $event->getController());
    }

    public function testUsesCustomRedirectRouteFromAttribute(): void
    {
        // Arrange
        $provider = $this->createMock(ApplicationReadinessProvider::class);
        $provider->method('isApplicationReady')->willReturn(false);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $listener = new ApplicationReadinessListener($provider, $urlGenerator);
        $controller = new CustomAttributeController();

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

        // Assert
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with('custom_route')
            ->willReturn('/custom/path')
        ;

        // Act
        $listener($event);

        // Assert
        $newController = $event->getController();
        $response = $newController();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/custom/path', $response->getTargetUrl());

        $flashes = $session->getFlashBag()->get('error');
        self::assertSame('Custom message', $flashes[0]);
    }
}
