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

namespace Inventory\Entities\VO;

/**
 * Represents a single packaging level (parcel, subPackage, or consumerUnit).
 *
 * This is a snapshot of packaging information stored with the inventory item
 * to preserve historical data even if the article's packaging changes.
 */
final readonly class PackagingLevel
{
    /**
     * @param array{unitLabel: string, unitAbbreviation: string, quantity: float} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['unitLabel'],
            $data['unitAbbreviation'],
            $data['quantity'],
        );
    }

    public function __construct(
        public string $unitLabel,
        public string $unitAbbreviation,
        public float $quantity,
    ) {
    }

    /**
     * @return array{unitLabel: string, unitAbbreviation: string, quantity: float}
     */
    public function toArray(): array
    {
        return [
            'unitLabel' => $this->unitLabel,
            'unitAbbreviation' => $this->unitAbbreviation,
            'quantity' => $this->quantity,
        ];
    }
}
