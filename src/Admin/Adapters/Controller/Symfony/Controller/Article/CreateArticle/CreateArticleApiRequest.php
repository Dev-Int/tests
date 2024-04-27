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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\CreateArticle;

use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Admin\Entities\Tax\Tax;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\UseCases\Article\CreateArticle\CreateArticleRequest;

final class CreateArticleApiRequest implements CreateArticleRequest
{
    /**
     * @param array<ZoneStorage>               $zoneStorages
     * @param array<array{string, float}|null> $packaging
     */
    public function __construct(
        public string $name,
        public Supplier $supplier,
        public array $packaging,
        public float $amount,
        public Tax $tax,
        public float $minStock,
        public array $zoneStorages,
        public FamilyLog $familyLog,
        public float $quantity,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function supplier(): Supplier
    {
        return $this->supplier;
    }

    public function packaging(): array
    {
        return $this->packaging;
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function tax(): Tax
    {
        return $this->tax;
    }

    public function minStock(): float
    {
        return $this->minStock;
    }

    public function zoneStorages(): array
    {
        return $this->zoneStorages;
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }
}
