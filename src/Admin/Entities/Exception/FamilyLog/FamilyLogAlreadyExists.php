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

namespace Admin\Entities\Exception\FamilyLog;

use Shared\Entities\Exception\DomainException;
use Shared\Entities\Exception\ExceptionSerializableTrait;

final class FamilyLogAlreadyExists extends DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const MESSAGE = 'FamilyLog already exists.';

    public function __construct(private readonly string $name, ?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, DomainException::BAD_ENTITY_CODE, $previous);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     *
     * @codeCoverageIgnore
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'name' => $this->name,
        ];
    }
}
