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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Admin\Entities\Event\DomainEvent;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework, ContainerConfigurator $container): void {
    $messenger = $framework->messenger();

    $messenger->transport('admin_transport')
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->failureTransport('admin_transport_failed')
        ->retryStrategy()
        ->maxRetries(3)
        ->delay(1000)
        ->multiplier(2)
    ;
    $messenger
        ->transport('admin_transport_failed')
        ->dsn('doctrine://default?queue_name=admin_transport_failed')
    ;

    $messenger->routing(DomainEvent::class)->senders(['admin_transport']);

    if ($container->env() === 'test') {
        $messenger->transport('admin_transport')->dsn('in-memory://');
    }
};
