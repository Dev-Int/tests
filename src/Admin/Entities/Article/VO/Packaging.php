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

namespace Admin\Entities\Article\VO;

use Admin\Entities\Unit\Unit;

final readonly class Packaging
{
    /**
     * @param array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null} $packages
     */
    public static function fromArray(array $packages): self
    {
        $parcel = Storage::fromArray($packages[0])->toArray();
        $subPackage = $packages[1] !== null ? Storage::fromArray($packages[1])->toArray() : null;
        $consumerUnit = $packages[2] !== null ? Storage::fromArray($packages[2])->toArray() : null;

        return new self($parcel, $subPackage, $consumerUnit);
    }

    /**
     * @param array{Unit, float}      $parcel
     * @param array{Unit, float}|null $subPackage
     * @param array{Unit, float}|null $consumerUnit
     */
    public function __construct(
        private array $parcel,
        private ?array $subPackage = null,
        private ?array $consumerUnit = null
    ) {
    }

    /**
     * @return array{Unit, float}
     */
    public function parcel(): array
    {
        return $this->parcel;
    }

    /**
     * @return array{Unit, float}|null
     */
    public function subPackage(): ?array
    {
        return $this->subPackage;
    }

    /**
     * @return array{Unit, float}|null
     */
    public function consumerUnit(): ?array
    {
        return $this->consumerUnit;
    }
}
