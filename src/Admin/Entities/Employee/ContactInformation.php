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

use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\PhoneField;

final readonly class ContactInformation
{
    public function __construct(
        public EmailField $email,
        public PhoneField $phone
    ) {
    }
}
