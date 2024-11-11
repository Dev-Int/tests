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
use Symfony\Contracts\Translation\TranslatorInterface;

final class CompatibleReassignFamilyLogsValidator extends ConstraintValidator
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof ReAssignArticleSupplierDto) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException($value, ReAssignArticleSupplierDto::class);
            // @codeCoverageIgnoreEnd
        }
        if (!$constraint instanceof CompatibleReassignFamilyLogs) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException($constraint, CompatibleReassignFamilyLogs::class);
            // @codeCoverageIgnoreEnd
        }

        $supplierFamilyLog = $value->supplier->familyLog();
        $familyLog = $value->familyLog;
        $checkFamilyLog = $supplierFamilyLog->isCompatible($familyLog);
        if ($checkFamilyLog === false) {
            $this->context
                ->buildViolation(
                    $this->translator->trans('article.form.incompatibleFamilyLogMessage', [], 'validators')
                )
                ->setParameter('{{ field }}', $this->translator->trans('admin.article.form.familyLog.label'))
                ->setParameter('{{ field_family }}', $familyLog->label())
                ->setParameter('{{ supplier_family }}', $supplierFamilyLog->label())
                ->atPath('familyLog')
                ->addViolation()
            ;
        }

        foreach ($value->zoneStorages as $zoneStorage) {
            $zoneStorageFamilyLog = $zoneStorage->familyLog();
            $checkZone = $supplierFamilyLog->isCompatible($zoneStorageFamilyLog);
            if ($checkZone === false) {
                $this->context
                    ->buildViolation(
                        $this->translator->trans('article.form.incompatibleFamilyLogMessage', [], 'validators')
                    )
                    ->setParameter('{{ field }}', $this->translator->trans('admin.article.form.zoneStorages.label'))
                    ->setParameter('{{ field_family }}', $zoneStorageFamilyLog->label())
                    ->setParameter('{{ supplier_family }}', $supplierFamilyLog->label())
                    ->atPath('zoneStorages')
                    ->addViolation()
                ;
            }
        }
    }
}
