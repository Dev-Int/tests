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

use Shared\Entities\Exception\DomainException;
use Shared\Entities\Exception\ExceptionSerializableTrait;
use Shared\Entities\ResourceUuid;

final class UserAlreadyDisabled extends DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const string MESSAGE = 'User is already disabled.';

    public function __construct(private readonly ResourceUuid $userUuid)
    {
        parent::__construct(self::MESSAGE, DomainException::CONFLICT_CODE);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'userUuid' => $this->userUuid->toString(),
        ];
    }
}
