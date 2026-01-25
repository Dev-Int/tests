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

namespace Shared\Entities\VO;

use Shared\Entities\Exception\InvalidEmailException;

final readonly class EmailField
{
    public static function fromString(string $email): self
    {
        return new self($email);
    }

    private function __construct(private string $value)
    {
        if (filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidEmailException($this->value);
        }
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $email): bool
    {
        return $this->value === $email->value;
    }
}
