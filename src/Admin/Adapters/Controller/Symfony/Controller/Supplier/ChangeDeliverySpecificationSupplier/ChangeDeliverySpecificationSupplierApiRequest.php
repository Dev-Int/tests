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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\ChangeDeliverySpecificationSupplier;

use Admin\Entities\FamilyLog\FamilyLog;
use Admin\UseCases\Supplier\ChangeDeliverySpecification\ChangeDeliverySpecificationSupplierRequest;

final class ChangeDeliverySpecificationSupplierApiRequest implements ChangeDeliverySpecificationSupplierRequest
{
    /**
     * @param array<int> $orderDays
     */
    public function __construct(
        public FamilyLog $familyLog,
        public int $delayDelivery,
        public array $orderDays,
        public string $slug
    ) {
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    public function delayDelivery(): int
    {
        return $this->delayDelivery;
    }

    public function orderDays(): array
    {
        return $this->orderDays;
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
