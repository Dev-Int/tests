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

namespace Auth\Entities\Exception;

use Shared\Entities\Exception\ExceptionSerializableTrait;

final class EmailAlreadyExists extends \DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const string MESSAGE = 'Email already exists.';

    public function __construct(private readonly string $email)
    {
        parent::__construct(self::MESSAGE);
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'email' => $this->email,
        ];
    }
}
