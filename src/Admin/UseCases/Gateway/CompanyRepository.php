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

namespace Admin\UseCases\Gateway;

use Admin\Entities\Company;

interface CompanyRepository
{
    public function save(Company $company): void;

    public function hasCompany(): bool;

    public function findByName(string $name): Company;

    public function update(Company $company): void;
}
