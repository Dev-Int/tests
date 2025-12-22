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
use Shared\Entities\Exception\ExceptionSerializableTrait;
use Shared\Entities\ResourceUuid;

final class InventoryAlreadyActiveForZone extends DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const string MESSAGE = 'Inventory is already active for zone(s).';

    /**
     * @param array<ResourceUuid> $zoneStorageIds
     */
    public function __construct(private readonly array $zoneStorageIds)
    {
        parent::__construct(self::MESSAGE, DomainException::INVALID_ARGUMENT_CODE);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>>
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'zoneStorageIds' => $this->zoneStorageIds,
        ];
    }
}
