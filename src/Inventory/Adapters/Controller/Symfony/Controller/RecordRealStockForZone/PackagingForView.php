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

namespace Inventory\Adapters\Controller\Symfony\Controller\RecordRealStockForZone;

final readonly class PackagingForView
{
    public function __construct(
        public PackagingLevelForView $parcel,
        public ?PackagingLevelForView $subPackage = null,
        public ?PackagingLevelForView $consumerUnit = null,
    ) {
    }

    public function hasMultipleLevels(): bool
    {
        return $this->subPackage instanceof PackagingLevelForView || $this->consumerUnit instanceof PackagingLevelForView;
    }
}
