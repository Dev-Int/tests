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

final class InvalidEmail extends \DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const MESSAGE = 'L\'adresse mail saisie n\'est pas valide.';

    public function __construct(private readonly string $email, ?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 400, $previous);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'email' => $this->email,
        ];
    }
}
