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

namespace Shared\Entities\Exception;

final class NegativeQuantity extends DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const string MESSAGE = 'The value cannot be negative.';

    public function __construct(private readonly float $value)
    {
        parent::__construct(self::MESSAGE, DomainException::INVALID_ARGUMENT_CODE);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     *
     * @codeCoverageIgnore
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'value' => $this->value,
        ];
    }
}
