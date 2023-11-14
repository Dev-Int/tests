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

namespace Article\Entities\Exception;

use Shared\Entities\Exception\ExceptionSerializableTrait;

final class InvalidUnitException extends \DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const MESSAGE = 'L\'unité ne correspond pas à une unité valide.';

    public function __construct(private readonly string $unit, ?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 0, $previous);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'unit' => $this->unit,
        ];
    }
}
