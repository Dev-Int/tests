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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\Validator;

use Admin\Adapters\Controller\Symfony\Controller\Article\ReAssignArticleSupplier\ReAssignArticleSupplierDto;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class CompatibleFamilyLogsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof ReAssignArticleSupplierDto) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException($value, ReAssignArticleSupplierDto::class);
            // @codeCoverageIgnoreEnd
        }
        if (!$constraint instanceof CompatibleFamilyLogs) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException($constraint, CompatibleFamilyLogs::class);
            // @codeCoverageIgnoreEnd
        }

        $supplierFamilyLog = $value->supplier->familyLog()->toDomain();
        $familyLog = $value->familyLog->toDomain();
        $checkFamilyLog = $supplierFamilyLog->isCompatible($familyLog);
        if ($checkFamilyLog === false) {
            $this->context->buildViolation($constraint->incompatibleFamilyLogMessage)
                ->setParameter('{{ field }}', 'familyLog')
                ->setParameter('{{ field_family }}', $familyLog->label()->toString())
                ->setParameter('{{ supplier_family }}', $supplierFamilyLog->label()->toString())
                ->atPath('familyLog')
                ->addViolation()
            ;
        }

        foreach ($value->zoneStorages as $zoneStorage) {
            $zoneStorageFamilyLog = $zoneStorage->familyLog()->toDomain();
            $checkZone = $supplierFamilyLog->isCompatible($zoneStorageFamilyLog);
            if ($checkZone === false) {
                $this->context->buildViolation($constraint->incompatibleFamilyLogMessage)
                    ->setParameter('{{ field }}', 'zoneStorages')
                    ->setParameter('{{ field_family }}', $zoneStorageFamilyLog->label()->toString())
                    ->setParameter('{{ supplier_family }}', $supplierFamilyLog->label()->toString())
                    ->atPath('zoneStorages')
                    ->addViolation()
                ;
            }
        }
    }
}
