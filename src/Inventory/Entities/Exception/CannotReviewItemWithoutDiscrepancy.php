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

namespace Inventory\Entities\Exception;

use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;

final class CannotReviewItemWithoutDiscrepancy extends DomainException implements \JsonSerializable
{
    public const string MESSAGE = 'Cannot review an item that has no discrepancy.';

    public function __construct(
        private readonly ResourceUuid $articleUuid,
        private readonly ResourceUuid $zoneStorageUuid,
    ) {
        parent::__construct(self::MESSAGE, DomainException::INVALID_ARGUMENT_CODE);
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'articleUuid' => $this->articleUuid->toString(),
            'zoneStorageUuid' => $this->zoneStorageUuid->toString(),
        ];
    }
}
