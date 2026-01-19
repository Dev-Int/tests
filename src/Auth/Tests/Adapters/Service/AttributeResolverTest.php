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

namespace Auth\Tests\Adapters\Service;

use Auth\Adapters\Service\AttributeResolver;
use Auth\Contracts\Attribute\RequireAuthenticated;
use Auth\Contracts\Attribute\RequireRole;
use Auth\Tests\Adapters\EventListener\Fixtures\BothAttributesController;
use Auth\Tests\Adapters\EventListener\Fixtures\CustomAuthAttributeController;
use Auth\Tests\Adapters\EventListener\Fixtures\NoAttributeController;
use Auth\Tests\Adapters\EventListener\Fixtures\RequireAuthenticatedController;
use Auth\Tests\Adapters\EventListener\Fixtures\RequireRoleController;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 *
 * @covers \Auth\Adapters\Service\AttributeResolver
 */
final class AttributeResolverTest extends TestCase
{
    private AttributeResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new AttributeResolver();
    }

    public function testResolvesRequireAuthenticatedAttributeFromInvokableController(): void
    {
        $controller = new RequireAuthenticatedController();

        $attribute = $this->resolver->resolve($controller, RequireAuthenticated::class);

        self::assertInstanceOf(RequireAuthenticated::class, $attribute);
        self::assertSame('auth_login', $attribute->redirectRoute);
        self::assertSame('auth.login_required', $attribute->flashMessage);
    }

    public function testResolvesRequireRoleAttributeFromInvokableController(): void
    {
        $controller = new RequireRoleController();

        $attribute = $this->resolver->resolve($controller, RequireRole::class);

        self::assertInstanceOf(RequireRole::class, $attribute);
        self::assertSame('ROLE_ADMIN', $attribute->role);
    }

    public function testResolvesCustomAttributeValues(): void
    {
        $controller = new CustomAuthAttributeController();

        $attribute = $this->resolver->resolve($controller, RequireAuthenticated::class);

        self::assertInstanceOf(RequireAuthenticated::class, $attribute);
        self::assertSame('custom_login', $attribute->redirectRoute);
        self::assertSame('Custom login message', $attribute->flashMessage);
    }

    public function testReturnsNullWhenNoAttributePresent(): void
    {
        $controller = new NoAttributeController();

        $attribute = $this->resolver->resolve($controller, RequireAuthenticated::class);

        self::assertNull($attribute);
    }

    public function testResolvesMultipleAttributesFromSameController(): void
    {
        $controller = new BothAttributesController();

        $authAttribute = $this->resolver->resolve($controller, RequireAuthenticated::class);
        $roleAttribute = $this->resolver->resolve($controller, RequireRole::class);

        self::assertInstanceOf(RequireAuthenticated::class, $authAttribute);
        self::assertInstanceOf(RequireRole::class, $roleAttribute);
        self::assertSame('ROLE_ADMIN', $roleAttribute->role);
    }

    public function testResolvesAttributeFromArrayCallable(): void
    {
        $controller = [new RequireAuthenticatedController(), '__invoke'];

        $attribute = $this->resolver->resolve($controller, RequireAuthenticated::class);

        self::assertInstanceOf(RequireAuthenticated::class, $attribute);
    }

    public function testReturnsNullForStringCallable(): void
    {
        // String callables (function names) are not supported
        $controller = 'strlen';

        $attribute = $this->resolver->resolve($controller, RequireAuthenticated::class);

        self::assertNull($attribute);
    }
}
