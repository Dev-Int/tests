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

namespace Admin\UseCases\Company\UpdateCompany;

interface UpdateCompanyRequest
{
    public function name(): string;

    public function address(): string;

    public function postalCode(): string;

    public function town(): string;

    public function country(): string;

    public function phone(): string;

    public function email(): string;

    public function contact(): string;
}
