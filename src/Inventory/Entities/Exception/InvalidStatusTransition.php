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

use Inventory\Entities\VO\InventoryStatus;
use Shared\Entities\Exception\DomainException;

final class InvalidStatusTransition extends DomainException implements \JsonSerializable
{
    public const string MESSAGE = 'Invalid status transition.';

    public function __construct(
        private readonly InventoryStatus $fromStatus,
        private readonly InventoryStatus $toStatus
    ) {
        parent::__construct(self::MESSAGE, DomainException::INVALID_ARGUMENT_CODE);
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'fromStatus' => $this->fromStatus->value,
            'toStatus' => $this->toStatus->value,
        ];
    }
}
