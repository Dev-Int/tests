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

namespace Article\Entities\VO;

use Article\Entities\Exception\InvalidPackageException;

final class Packaging
{
    /** @var array{string, float} */
    private array $parcel;

    /** @var array{string, float}|null */
    private ?array $consumerUnit;

    /** @var array{string, float}|null */
    private ?array $subPackage;

    /**
     * @param array<array{string, float}|null> $packages
     */
    public static function fromArray(array $packages): self
    {
        if (null === $packages[0]) {
            throw new InvalidPackageException();
        }
        $parcel = Storage::fromArray($packages[0])->toArray();
        $subPackage = null;
        $consumerUnit = null;

        for ($i = 1; $i < 3; ++$i) {
            if (null !== $packages[$i]) {
                if (1 === $i) {
                    $subPackage = Storage::fromArray($packages[$i])->toArray();
                }
                if (2 === $i) {
                    $consumerUnit = Storage::fromArray($packages[$i])->toArray();
                }
            }
        }

        return new self($parcel, $subPackage, $consumerUnit);
    }

    /**
     * @param array{string, float}      $parcel
     * @param array{string, float}|null $subPackage
     * @param array{string, float}|null $consumerUnit
     */
    public function __construct(array $parcel, ?array $subPackage = null, ?array $consumerUnit = null)
    {
        $this->parcel = $parcel;
        $this->subPackage = $subPackage;
        $this->consumerUnit = $consumerUnit;
    }

    /**
     * @return array{string, float}
     */
    public function parcel(): array
    {
        return $this->parcel;
    }

    /**
     * @return array{string, float}|null
     */
    public function subPackage(): ?array
    {
        return $this->subPackage;
    }

    /**
     * @return array{string, float}|null
     */
    public function consumerUnit(): ?array
    {
        return $this->consumerUnit;
    }
}
