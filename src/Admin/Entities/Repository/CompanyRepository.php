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

namespace Admin\Entities\Repository;

use Admin\Entities\Company;
use Admin\Entities\Exception\Company\CompanyNotFound;

interface CompanyRepository
{
    public function save(Company $company): void;

    public function hasCompany(): bool;

    /**
     * @throws CompanyNotFound
     */
    public function getByName(string $name): Company;

    /**
     * @throws CompanyNotFound
     */
    public function update(Company $company): void;
}
