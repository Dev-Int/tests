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

namespace Admin\Entities\Exception\Employee;

use Shared\Entities\VO\EmailField;

final class EmployeeAlreadyExists extends \DomainException implements \JsonSerializable
{
    public const string MESSAGE = 'Un employé avec cet email existe déjà.';

    public function __construct(private readonly EmailField $email)
    {
        parent::__construct(self::MESSAGE);
    }

    /**
     * @return iterable<string, array<int, string>|int|string>
     */
    public function jsonSerialize(): iterable
    {
        return [
            'email' => $this->email->toString(),
        ];
    }
}
