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

namespace Admin\Entities\Employee;

use Admin\Entities\Exception\Employee\EmployeeAlreadyDisabled;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final class Employee
{
    public static function create(
        ResourceUuid $uuid,
        NameField $firstName,
        NameField $lastName,
        ContactInformation $contactInformation,
        NameField $position,
        NameField $department,
        \DateTimeImmutable $hiredAt,
        ResourceUuid $userUuid,
    ): self {
        return new self(
            uuid: $uuid,
            firstName: $firstName,
            lastName: $lastName,
            contactInformation: $contactInformation,
            position: $position,
            department: $department,
            hiredAt: $hiredAt,
            userUuid: $userUuid,
            createdAt: ClockFactory::clock()->now(),
            updatedAt: ClockFactory::clock()->now(),
        );
    }

    public static function reconstitute(
        ResourceUuid $uuid,
        NameField $firstName,
        NameField $lastName,
        ContactInformation $contactInformation,
        NameField $position,
        NameField $department,
        \DateTimeImmutable $hiredAt,
        ResourceUuid $userUuid,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $disabledAt = null,
    ): self {
        return new self(
            uuid: $uuid,
            firstName: $firstName,
            lastName: $lastName,
            contactInformation: $contactInformation,
            position: $position,
            department: $department,
            hiredAt: $hiredAt,
            userUuid: $userUuid,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            disabledAt: $disabledAt,
        );
    }

    private function __construct(
        private readonly ResourceUuid $uuid,
        private readonly NameField $firstName,
        private readonly NameField $lastName,
        private ContactInformation $contactInformation,
        private NameField $position,
        private NameField $department,
        private readonly \DateTimeImmutable $hiredAt,
        private readonly ResourceUuid $userUuid,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $disabledAt = null,
    ) {
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function firstName(): NameField
    {
        return $this->firstName;
    }

    public function lastName(): NameField
    {
        return $this->lastName;
    }

    public function contactInformation(): ContactInformation
    {
        return $this->contactInformation;
    }

    public function position(): NameField
    {
        return $this->position;
    }

    public function department(): NameField
    {
        return $this->department;
    }

    public function hiredAt(): \DateTimeImmutable
    {
        return $this->hiredAt;
    }

    public function userUuid(): ResourceUuid
    {
        return $this->userUuid;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function disabledAt(): ?\DateTimeImmutable
    {
        return $this->disabledAt;
    }

    public function updatePhone(PhoneField $phone): void
    {
        $phoneChanged = $this->contactInformation->phone()->toNumber()
            !== $phone->toNumber();

        if (!$phoneChanged) {
            return;
        }

        $this->contactInformation = ContactInformation::fromFields(
            $this->contactInformation->email(),
            $phone
        );
        $this->updatedAt = ClockFactory::clock()->now();
    }

    public function updatePosition(NameField $position, NameField $department): void
    {
        // Vérifier si la position ou le département ont changé
        $positionChanged = $this->position->toString() !== $position->toString()
            || $this->department->toString() !== $department->toString();

        if (!$positionChanged) {
            return; // Aucun changement, pas de mise à jour
        }

        $this->position = $position;
        $this->department = $department;
        $this->updatedAt = ClockFactory::clock()->now();
    }

    /**
     * Disable this employee (soft delete).
     *
     * @todo Future RH Feature: Ajouter audit trail complet
     *       - disabledBy (ResourceUuid): Qui a désactivé l'employé ?
     *       - disabledReason (string): Motif RH (démission, licenciement, etc.)
     *       - Conformité légale: Traçabilité actions RH
     *       - Migration DB nécessaire: disabled_by UUID, disabled_reason TEXT
     *       - Adapter tous les tests utilisant disable()
     *       - Voir PR #255 review point 3 pour détails complets
     *
     * @throws EmployeeAlreadyDisabled
     */
    public function disable(): void
    {
        if (!$this->isActive()) {
            throw new EmployeeAlreadyDisabled($this->uuid);
        }
        $this->disabledAt = ClockFactory::clock()->now();
        $this->updatedAt = ClockFactory::clock()->now();
    }

    public function isActive(): bool
    {
        return !$this->disabledAt instanceof \DateTimeImmutable;
    }
}
