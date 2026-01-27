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

namespace Admin\Tests\DataBuilder;

use Admin\Entities\Employee\ContactInformation;
use Admin\Entities\Employee\Employee;
use Admin\Entities\VO\EmployeeStatus;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final class EmployeeDataBuilder
{
    private ResourceUuid $uuid;
    private NameField $firstName;
    private NameField $lastName;
    private EmailField $email;
    private PhoneField $phone;
    private NameField $position;
    private NameField $department;
    private \DateTimeImmutable $hiredAt;
    private EmployeeStatus $status = EmployeeStatus::ACTIVE;
    private ?ResourceUuid $userUuid;
    private ?\DateTimeImmutable $disabledAt = null;

    public static function anEmployee(): self
    {
        return new self();
    }

    private function __construct()
    {
        $this->uuid = ResourceUuid::generate();
        $this->firstName = NameField::fromString('John');
        $this->lastName = NameField::fromString('Doe');
        $this->email = EmailField::fromString('john.doe@example.com');
        $this->phone = PhoneField::fromString('0612345678');
        $this->position = NameField::fromString('Developer');
        $this->department = NameField::fromString('IT');
        $this->hiredAt = new \DateTimeImmutable('2024-01-15');
        $this->userUuid = ResourceUuid::generate();
    }

    public function withUuid(ResourceUuid $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function withFirstName(string $firstName): self
    {
        $this->firstName = NameField::fromString($firstName);

        return $this;
    }

    public function withLastName(string $lastName): self
    {
        $this->lastName = NameField::fromString($lastName);

        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->email = EmailField::fromString($email);

        return $this;
    }

    public function withPhone(string $phone): self
    {
        $this->phone = PhoneField::fromString($phone);

        return $this;
    }

    public function withPosition(string $position): self
    {
        $this->position = NameField::fromString($position);

        return $this;
    }

    public function withDepartment(string $department): self
    {
        $this->department = NameField::fromString($department);

        return $this;
    }

    public function withHiredAt(\DateTimeImmutable $hiredAt): self
    {
        $this->hiredAt = $hiredAt;

        return $this;
    }

    public function withStatus(EmployeeStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function withUserUuid(?ResourceUuid $userUuid): self
    {
        $this->userUuid = $userUuid;

        return $this;
    }

    public function disabled(): self
    {
        $this->disabledAt = new \DateTimeImmutable();

        return $this;
    }

    public function build(): Employee
    {
        if (!$this->userUuid instanceof ResourceUuid) {
            throw new \LogicException('userUuid must be set before building Employee');
        }

        return Employee::reconstitute(
            $this->uuid,
            $this->firstName,
            $this->lastName,
            new ContactInformation($this->email, $this->phone),
            $this->position,
            $this->department,
            $this->hiredAt,
            $this->status,
            $this->userUuid,
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-01-15'),
            $this->disabledAt,
        );
    }
}
