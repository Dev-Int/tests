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

final class InvalidCollectionIterationException extends \DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Invalid iteration', 0, $previous);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     *
     * @codeCoverageIgnore
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson();
    }
}
