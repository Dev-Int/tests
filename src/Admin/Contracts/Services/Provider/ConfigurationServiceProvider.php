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

interface ConfigurationServiceProvider
{
    public const string ROUTE_NAME = ConfigurationController::ROUTE_NAME;

    public function isApplicationReady(): bool;
}
