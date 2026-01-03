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
     * @param array{Unit, float}      $consumerUnit
     * @param array{Unit, float}|null $subPackage
     * @param array{Unit, float}|null $parcel
     */
    public function __construct(
        private array $consumerUnit,
        private ?array $subPackage = null,
        private ?array $parcel = null
    ) {
    }

    /**
     * @return array{Unit, float}
     */
    public function consumerUnit(): array
    {
        return $this->consumerUnit;
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
    public function parcel(): ?array
    {
        return $this->parcel;
    }
}
