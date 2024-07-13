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

final class IsAlreadyChildException extends \DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    public const MESSAGE = 'The logistic family is already child of parent.';

    public function __construct(
        private readonly string $childSlug,
        private readonly string $parentSlug,
        ?\Throwable $previous = null
    ) {
        parent::__construct(self::MESSAGE, 0, $previous);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     *
     * @codeCoverageIgnore
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'childSlug' => $this->childSlug,
            'parentSlug' => $this->parentSlug,
        ];
    }
}
