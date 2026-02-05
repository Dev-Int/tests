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

use Shared\Entities\Exception\InvalidPhoneException;

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
            // @codeCoverageIgnoreStart
            throw new InvalidPhoneException($phoneNumber);
            // @codeCoverageIgnoreEnd
        }

        $phoneToCheck = preg_replace('/[-\s]/', '', $phoneSanitized);
        if ($phoneToCheck === null) {
            // @codeCoverageIgnoreStart
            throw new InvalidPhoneException($phoneNumber);
            // @codeCoverageIgnoreEnd
        }

        if (preg_match('/^(\+\d{2}|0)([12345679]\d{8})$/', $phoneToCheck) !== 1) {
            throw new InvalidPhoneException($phoneNumber);
        }

        $this->phoneNumber = $phoneSanitized;
    }

    public function toNumber(): string
    {
        return $this->phoneNumber;
    }
}
