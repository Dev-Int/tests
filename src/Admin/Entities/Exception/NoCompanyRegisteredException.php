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

namespace Admin\Entities\Exception;

use Shared\Entities\Exception\ExceptionSerializableTrait;

final class NoCompanyRegisteredException extends \DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    private const MESSAGE = 'No company is registered.';

    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 400, $previous);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson();
    }
}
