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

final class TokenNotFound extends \DomainException implements \JsonSerializable
{
    public const MESSAGE = 'Token not found.';

    public function __construct(private readonly string $token)
    {
        parent::__construct(self::MESSAGE);
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
