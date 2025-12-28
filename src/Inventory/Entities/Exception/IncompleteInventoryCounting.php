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

final class IncompleteInventoryCounting extends DomainException implements \JsonSerializable
{
    public const string MESSAGE = 'All items must be counted before finishing. Some zones have uncounted items.';

    /**
     * @param array<ResourceUuid> $zonesWithUncountedItems
     */
    public function __construct(
        private readonly array $zonesWithUncountedItems,
    ) {
        parent::__construct(message: self::MESSAGE, code: DomainException::INVALID_ARGUMENT_CODE);
    }

    /**
     * @return array<ResourceUuid>
     */
    public function zonesWithUncountedItems(): array
    {
        return $this->zonesWithUncountedItems;
    }

    /**
     * @return array<string, array<string>|int|string>
     */
    public function jsonSerialize(): array
    {
        return [
            'message' => self::MESSAGE,
            'code' => $this->getCode(),
            'zonesWithUncountedItems' => array_map(
                static fn (ResourceUuid $zone): string => $zone->toString(),
                $this->zonesWithUncountedItems
            ),
        ];
    }
}
