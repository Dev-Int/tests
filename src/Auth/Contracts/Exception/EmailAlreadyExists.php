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

namespace Auth\Contracts\Exception;

use Shared\Entities\VO\EmailField;

final class EmailAlreadyExists extends \DomainException implements \JsonSerializable
{
    public const string MESSAGE = 'A user with this email already exists.';

    public function __construct(private readonly ?EmailField $email)
    {
        parent::__construct(self::MESSAGE);
    }

    /**
     * @return iterable<string, string|null>
     */
    public function jsonSerialize(): iterable
    {
        return [
            'email' => $this->email?->toString(),
        ];
    }
}
