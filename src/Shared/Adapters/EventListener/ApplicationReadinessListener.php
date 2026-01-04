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

namespace Shared\Adapters\EventListener;

use Shared\Adapters\Attribute\RequireApplicationReady;
use Shared\Contracts\ApplicationReadinessProvider;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(event: KernelEvents::CONTROLLER_ARGUMENTS)]
final readonly class ApplicationReadinessListener
{
    public function __construct(
        private ApplicationReadinessProvider $provider,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(ControllerArgumentsEvent $event): void
    {
        $attribute = $this->getRequireApplicationReadyAttribute($event->getController());

        if (!$attribute instanceof RequireApplicationReady) {
            return;
        }

        if ($this->provider->isApplicationReady()) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('error', $attribute->flashMessage);
        }
        $response = new RedirectResponse($this->urlGenerator->generate($attribute->redirectRoute));
        $event->setController(static fn (): RedirectResponse => $response);
    }

    /**
     * @param array<int, object|string>|callable $controller
     */
    private function getRequireApplicationReadyAttribute(array|callable $controller): ?RequireApplicationReady
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

        // Check method-level attribute first
        if ($reflectionClass->hasMethod($method)) {
            $reflectionMethod = $reflectionClass->getMethod($method);
            $methodAttributes = $reflectionMethod->getAttributes(RequireApplicationReady::class);
            if ($methodAttributes !== []) {
                return $methodAttributes[0]->newInstance();
            }
        }

        // Fall back to class-level attribute
        $classAttributes = $reflectionClass->getAttributes(RequireApplicationReady::class);
        if ($classAttributes !== []) {
            return $classAttributes[0]->newInstance();
        }

        return null;
    }
}
