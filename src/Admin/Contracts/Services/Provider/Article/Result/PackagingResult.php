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
 * - parcel: The base package level (required)
 * - subPackage: Intermediate packaging level (optional)
 * - consumerUnit: The smallest unit level (optional)
 */
final readonly class PackagingResult
{
    public function __construct(
        public PackagingLevelResult $parcel,
        public ?PackagingLevelResult $subPackage = null,
        public ?PackagingLevelResult $consumerUnit = null,
    ) {
    }
}
