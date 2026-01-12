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

use Shared\Contracts\ApplicationReadinessProvider;

interface ConfigurationServiceProvider extends ApplicationReadinessProvider
{
    public const string ROUTE_NAME = 'admin_configure';

    public function isCompanyConfigured(): bool;

    public function isUnitConfigured(): bool;

    public function isTaxConfigured(): bool;

    public function isApplicationConfigured(): bool;

    public function isFamilyLogConfigured(): bool;

    public function isZoneStorageConfigured(): bool;

    public function isSupplierConfigured(): bool;

    public function isArticleConfigured(): bool;
}
