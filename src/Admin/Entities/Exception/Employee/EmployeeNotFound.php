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

use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;

final class EmployeeNotFound extends \DomainException implements \JsonSerializable
{
    public const string MESSAGE = 'Employé introuvable.';

    public static function byEmail(EmailField $email): self
    {
        return new self($email);
    }

    public function __construct(private readonly EmailField|ResourceUuid $id)
    {
        parent::__construct(self::MESSAGE);
    }

    /**
     * @return iterable<string, string>
     */
    public function jsonSerialize(): iterable
    {
        if ($this->id instanceof EmailField) {
            return [
                'employeeEmail' => $this->id->toString(),
            ];
        }

        return [
            'employeeUserUuid' => $this->id->toString(),
        ];
    }
}
