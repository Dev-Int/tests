<?php

declare(strict_types=1);

namespace Admin\Entities\Exception;

use Shared\Entities\Exception\ExceptionSerializableTrait;

final class FamilyLogAlreadyExistsException extends \DomainException implements \JsonSerializable
{
    use ExceptionSerializableTrait;

    private const MESSAGE = 'FamilyLog already exists.';

    public function __construct(private readonly string $name, ?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 0, $previous);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     */
    public function jsonSerialize(): iterable
    {
        return $this->toJson() + [
            'name' => $this->name,
        ];
    }
}
