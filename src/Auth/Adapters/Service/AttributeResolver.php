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

namespace Auth\Adapters\Service;

/**
 * Resolves PHP attributes from controller classes and methods.
 *
 * This service extracts attribute resolution logic to allow future caching
 * optimization (e.g., APCu cache) without modifying the AuthenticationListener.
 */
final readonly class AttributeResolver
{
    /**
     * @template T of object
     *
     * @param array<int, object|string>|callable $controller
     * @param class-string<T>                    $attributeClass
     *
     * @return T|null
     */
    public function resolve(array|callable $controller, string $attributeClass): ?object
    {
        if (\is_array($controller)) {
            [$controllerObject, $method] = $controller;
        } elseif (\is_object($controller)) {
            $controllerObject = $controller;
            $method = '__invoke';
        } else {
            return null;
        }

        $reflectionClass = new \ReflectionClass($controllerObject);

        // Method attributes take precedence over class attributes
        if ($reflectionClass->hasMethod($method)) {
            $reflectionMethod = $reflectionClass->getMethod($method);
            $methodAttributes = $reflectionMethod->getAttributes($attributeClass);
            if ($methodAttributes !== []) {
                return $methodAttributes[0]->newInstance();
            }
        }

        $classAttributes = $reflectionClass->getAttributes($attributeClass);
        if ($classAttributes !== []) {
            return $classAttributes[0]->newInstance();
        }

        return null;
    }
}
