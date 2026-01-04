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

    $services->alias(
        id: 'Admin\Contracts\Services\Provider\Article\ArticleProvider',
        referencedId: 'Admin\Adapters\Gateway\Contracts\Provider\Article\ArticleProvider'
    )
        ->public()
    ;

    $services->alias(
        id: 'Admin\Contracts\Services\Updater\Article\ArticleQuantityUpdater',
        referencedId: 'Admin\Adapters\Gateway\Contracts\Updater\Article\ArticleQuantityUpdater'
    )
        ->public()
    ;

    $services->alias(
        id: 'Shared\Contracts\ApplicationReadinessProvider',
        referencedId: 'Admin\Adapters\Gateway\ConfigurationService'
    )
        ->public()
    ;
};
