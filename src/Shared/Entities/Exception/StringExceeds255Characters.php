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

final class StringExceeds255Characters extends \DomainException
{
    use ExceptionSerializableTrait;

    public const MESSAGE = 'Le texte saisie ne devrait pas excéder 255 caractères';

    public function __construct(private readonly string $text, ?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 400, $previous);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'text' => $this->text,
        ];
    }
}
