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

namespace Admin\UseCases\Employee\Exception;

use Shared\Entities\ResourceUuid;

final class UserAlreadyDisabled extends \DomainException implements \JsonSerializable
{
    public const string MESSAGE = 'User is already disabled.';

    public function __construct(private readonly ResourceUuid $userUuid)
    {
        parent::__construct(self::MESSAGE);
    }

    /**
     * @return iterable<string, string>
     */
    public function jsonSerialize(): iterable
    {
        return [
            'userUuid' => $this->userUuid->toString(),
        ];
    }
}
