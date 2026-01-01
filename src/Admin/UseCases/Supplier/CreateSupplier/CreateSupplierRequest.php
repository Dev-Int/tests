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

namespace Admin\UseCases\Supplier\CreateSupplier;

use Admin\Entities\FamilyLog\FamilyLog;

interface CreateSupplierRequest
{
    public function name(): string;

    public function streetAddress(): string;

    public function postalCode(): string;

    public function city(): string;

    public function country(): string;

    public function phone(): string;

    public function email(): string;

    public function contact(): string;

    public function cellphone(): string;

    public function familyLog(): FamilyLog;

    public function delayDelivery(): int;

    /**
     * @return array<int>
     */
    public function orderDays(): array;
}
