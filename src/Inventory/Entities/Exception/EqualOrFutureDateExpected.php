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

use Shared\Entities\Exception\ExceptionSerializableTrait;

final class EqualOrFutureDateExpected extends \DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const string MESSAGE = 'Inventory date must be today or in the future.';

    public function __construct(private readonly \DateTimeImmutable $date)
    {
        parent::__construct(self::MESSAGE);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toJson() + ['date' => $this->date->format('Y-m-d H:i:s')];
    }
}
