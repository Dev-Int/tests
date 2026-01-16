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

    $services->load(namespace: 'Auth\\', resource: __DIR__ . '/../../../Auth')
        ->exclude(__DIR__ . '/../../../Auth/{Frameworks,Entities,Tests}')
    ;

    $services->load(
        namespace: 'Auth\Entities\Repository\\',
        resource: __DIR__ . '/../../../Auth/Entities/Repository'
    );
};
