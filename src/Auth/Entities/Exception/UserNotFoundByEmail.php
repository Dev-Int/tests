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
use Shared\Entities\VO\EmailField;

final class UserNotFoundByEmail extends UserNotFound implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const string MESSAGE = 'User not found by email.';

    public function __construct(private readonly EmailField $email)
    {
        parent::__construct(self::MESSAGE);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'email' => $this->email->toString(),
        ];
    }
}
