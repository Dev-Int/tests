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

use Shared\Entities\Exception\InvalidEmail;

final readonly class EmailField
{
    public static function fromString(string $email): self
    {
        return new self($email);
    }

    private function __construct(private string $email)
    {
        if (filter_var($email, \FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidEmail($this->email);
        }
    }

    public function toString(): string
    {
        return $this->email;
    }
}
