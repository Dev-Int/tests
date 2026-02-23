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

namespace Admin\Adapters\Gateway;

use Admin\Entities\Event\DomainEvent;
use Admin\UseCases\Gateway\EventPublisher;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsAlias(EventPublisher::class)]
final readonly class SymfonyEventPublisher implements EventPublisher
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function publish(DomainEvent $event): void
    {
        $this->messageBus->dispatch($event);
    }
}
