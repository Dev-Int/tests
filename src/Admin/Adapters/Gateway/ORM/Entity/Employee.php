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

namespace Admin\Adapters\Gateway\ORM\Entity;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineEmployeeRepository;
use Admin\Entities\Employee\ContactInformation;
use Admin\Entities\Employee\Employee as EmployeeDomain;
use Admin\Entities\VO\EmployeeStatus;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

#[ORM\Entity(repositoryClass: DoctrineEmployeeRepository::class)]
#[ORM\Table(name: 'employees')]
#[ORM\Index(name: 'idx_employee_disabled_at', columns: ['disabled_at'])]
#[ORM\UniqueConstraint(name: 'uniq_employee_email', columns: ['email'])]
class Employee
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $uuid;

    #[ORM\Column(type: 'string', length: 255)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 255)]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 180)]
    private string $email;

    #[ORM\Column(type: 'string', length: 20)]
    private string $phone;

    #[ORM\Column(type: 'string', length: 255)]
    private string $position;

    #[ORM\Column(type: 'string', length: 255)]
    private string $department;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private \DateTimeImmutable $hiredAt;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status;

    #[ORM\Column(type: 'guid')]
    private string $userUuid;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetimetz_immutable', nullable: true)]
    private ?\DateTimeImmutable $disabledAt;

    public function fromDomain(EmployeeDomain $employee): self
    {
        $this->uuid = $employee->uuid()->toString();
        $this->firstName = $employee->firstName()->toString();
        $this->lastName = $employee->lastName()->toString();
        $this->email = $employee->contactInformation()->email->toString();
        $this->phone = $employee->contactInformation()->phone->toNumber();
        $this->position = $employee->position()->toString();
        $this->department = $employee->department()->toString();
        $this->hiredAt = $employee->hiredAt();
        $this->status = $employee->status()->value;
        $this->userUuid = $employee->userUuid()->toString();
        $this->createdAt = $employee->createdAt();
        $this->updatedAt = $employee->updatedAt();
        $this->disabledAt = $employee->disabledAt();

        return $this;
    }

    public function toDomain(): EmployeeDomain
    {
        return EmployeeDomain::reconstitute(
            uuid: ResourceUuid::fromString($this->uuid),
            firstName: NameField::fromString($this->firstName),
            lastName: NameField::fromString($this->lastName),
            contactInformation: new ContactInformation(
                email: EmailField::fromString($this->email),
                phone: PhoneField::fromString($this->phone)
            ),
            position: NameField::fromString($this->position),
            department: NameField::fromString($this->department),
            hiredAt: $this->hiredAt,
            status: EmployeeStatus::from($this->status),
            userUuid: ResourceUuid::fromString($this->userUuid),
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            disabledAt: $this->disabledAt,
        );
    }

    public function updateFromDomain(EmployeeDomain $employee): void
    {
        $this->firstName = $employee->firstName()->toString();
        $this->lastName = $employee->lastName()->toString();
        $this->email = $employee->contactInformation()->email->toString();
        $this->phone = $employee->contactInformation()->phone->toNumber();
        $this->position = $employee->position()->toString();
        $this->department = $employee->department()->toString();
        $this->status = $employee->status()->value;
        $this->updatedAt = $employee->updatedAt();
        $this->disabledAt = $employee->disabledAt();
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function fullName(): string
    {
        return \sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function position(): string
    {
        return $this->position;
    }

    public function department(): string
    {
        return $this->department;
    }

    public function hiredAt(): \DateTimeImmutable
    {
        return $this->hiredAt;
    }

    public function status(): string
    {
        return $this->status;
    }
}
