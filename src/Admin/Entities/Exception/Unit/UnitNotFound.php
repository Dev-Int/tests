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

namespace Admin\Entities\Exception\Unit;

use Shared\Entities\Exception\DomainException;
use Shared\Entities\Exception\ExceptionSerializableTrait;

final class UnitNotFound extends DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const MESSAGE = 'Unit not found.';

    public function __construct(private readonly string $slug, ?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, DomainException::NOT_FOUND_CODE, $previous);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     *
     * @codeCoverageIgnore
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'slug' => $this->slug,
        ];
    }
}
