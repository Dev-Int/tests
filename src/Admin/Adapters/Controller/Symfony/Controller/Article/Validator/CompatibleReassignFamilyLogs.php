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

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class CompatibleReassignFamilyLogs extends Constraint
{
    public string $incompatibleFamilyLogMessage = 'The {{ field }} logistic family "{{ field_family }}" is not compatible with the supplier logistic family: "{{ supplier_family }}"';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
