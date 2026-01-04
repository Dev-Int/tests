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

namespace Shared\Adapters\Exception;

use Shared\Entities\Exception\ExceptionSerializableTrait;

final class ApplicationNotAlreadyConfigured extends \RuntimeException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const string MESSAGE = 'Application is not already configured.';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
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
