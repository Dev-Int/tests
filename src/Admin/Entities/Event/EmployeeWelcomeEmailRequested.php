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

namespace Admin\Entities\Event;

use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;

/**
 * Domain event indicating that a welcome email should be sent to a new Employee.
 *
 * This event is published AFTER the Employee and User are successfully created
 * and persisted (after transaction commit). It triggers an asynchronous email
 * sending process with automatic retry on failure.
 */
final readonly class EmployeeWelcomeEmailRequested implements DomainEvent
{
    public function __construct(
        public ResourceUuid $employeeUuid,
        public EmailField $employeeEmail,
        public string $firstName,
        public string $resetUrl,
    ) {
    }
}
