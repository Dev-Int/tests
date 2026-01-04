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

namespace Admin\Contracts\Services\Provider;

use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Shared\Contracts\ApplicationReadinessProvider;

interface ConfigurationServiceProvider extends ApplicationReadinessProvider
{
    public const string ROUTE_NAME = ConfigurationController::ROUTE_NAME;
}
