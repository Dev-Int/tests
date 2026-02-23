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

namespace Auth\Adapters\EventListener;

use Auth\Adapters\Service\AttributeResolver;
use Auth\Contracts\Attribute\RequireAuthenticated;
use Auth\Contracts\Attribute\RequireRole;
use Auth\Contracts\CurrentUserProvider;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: KernelEvents::CONTROLLER_ARGUMENTS)]
final readonly class AuthenticationListener
{
    public function __construct(
        private CurrentUserProvider $userProvider,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private AttributeResolver $attributeResolver,
    ) {
    }

    public function __invoke(ControllerArgumentsEvent $event): void
    {
        $controller = $event->getController();

        $requireAuth = $this->attributeResolver->resolve($controller, RequireAuthenticated::class);
        $requireRole = $this->attributeResolver->resolve($controller, RequireRole::class);

        if ($requireAuth === null && $requireRole === null) {
            return;
        }

        if ($requireAuth !== null && !$this->userProvider->isAuthenticated()) {
            $this->redirectToLogin($event, $requireAuth);

            return;
        }

        if ($requireRole !== null) {
            if ($requireAuth === null && !$this->userProvider->isAuthenticated()) {
                $this->redirectToLogin($event, new RequireAuthenticated());

                return;
            }

            if (!$this->userProvider->hasRole($requireRole->role)) {
                throw new AccessDeniedException(
                    \sprintf('Access denied. Required role: %s', $requireRole->role)
                );
            }
        }
    }

    private function redirectToLogin(ControllerArgumentsEvent $event, RequireAuthenticated $attribute): void
    {
        $session = $event->getRequest()->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $message = $this->translator->trans($attribute->flashMessage, [], 'auth');
            $session->getFlashBag()->add('warning', $message);
        }
        $response = new RedirectResponse($this->urlGenerator->generate($attribute->redirectRoute));
        $event->setController(static fn (): RedirectResponse => $response);
    }
}
