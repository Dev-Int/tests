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

final class NoItemsSelectedForReview extends DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const string MESSAGE = 'No items selected for review.';

    public function __construct()
    {
        parent::__construct(self::MESSAGE, DomainException::INVALID_ARGUMENT_CODE);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson();
    }
}
