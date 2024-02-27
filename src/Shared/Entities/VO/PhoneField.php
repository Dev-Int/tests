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

use Shared\Entities\Exception\InvalidPhone;

final class PhoneField
{
    public static function fromString(string $phoneNumber): self
    {
        return new self($phoneNumber);
    }

    private function __construct(private string $phoneNumber)
    {
        $phoneSanitized = filter_var($phoneNumber, \FILTER_SANITIZE_NUMBER_INT);
        if ($phoneSanitized === false) {
            throw new InvalidPhone($phoneNumber);
        }

        $phoneToCheck = str_replace('-', '', $phoneSanitized);

        if (preg_match('/^(\+\d{2}|0)([12345679]\d{8})$/', $phoneToCheck) !== 1) {
            throw new InvalidPhone($phoneNumber);
        }

        $this->phoneNumber = $phoneSanitized;
    }

    public function toNumber(): string
    {
        return $this->phoneNumber;
    }
}
