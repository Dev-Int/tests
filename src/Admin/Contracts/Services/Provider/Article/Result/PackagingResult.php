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

namespace Admin\Contracts\Services\Provider\Article\Result;

/**
 * Represents the complete packaging structure of an article.
 *
 * - consumerUnit: The smallest unit level (required) - used for recipes
 * - subPackage: Intermediate packaging level (optional)
 * - parcel: The supplier package level (optional)
 */
final readonly class PackagingResult
{
    public function __construct(
        public PackagingLevelResult $consumerUnit,
        public ?PackagingLevelResult $subPackage = null,
        public ?PackagingLevelResult $parcel = null,
    ) {
    }
}
