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

use Admin\Adapters\Gateway\CachedConfigurationService;
use Admin\Adapters\Gateway\NotificationProvider;
use Shared\Contracts\ApplicationReadinessProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->private()
    ;

    $services->load(namespace: 'Admin\\', resource: __DIR__ . '/../../../Admin')
        ->exclude(__DIR__ . '/../../../Admin/{Frameworks,Entities,Tests}')
        ->exclude(__DIR__ . '/../../../Admin/Adapters/Gateway/ORM/Entity')
    ;

    $services->load(
        namespace: 'Admin\Entities\Repository\\',
        resource: __DIR__ . '/../../../Admin/Entities/Repository'
    );

    $services->load(
        namespace: 'Admin\UseCases\Gateway\Finder\\',
        resource: __DIR__ . '/../../../Admin/UseCases/Gateway/Finder'
    );

    $services->set(id: NotificationProvider::class)
        ->args([
            '$fromEmail' => '%env(EMAIL_FROM_ADDRESS)%',
            '$fromName' => '%env(EMAIL_FROM_NAME)%',
        ])
    ;

    // Utilise le service avec cache pour la vérification de configuration
    $services->alias(
        id: ApplicationReadinessProvider::class,
        referencedId: CachedConfigurationService::class
    );
};
