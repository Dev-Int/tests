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

namespace Auth\Entities\Repository;

use Auth\Entities\ResetPassword;
use Shared\Entities\ResourceUuid;

interface PasswordResetTokenRepository
{
    public function save(ResetPassword $token): void;

    public function create(ResetPassword $token): void;

    public function deleteByUserUuid(ResourceUuid $userUuid): void;
}
