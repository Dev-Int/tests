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

namespace Auth\UseCases\Gateway;

use Auth\Entities\VO\HashedPassword;

interface PasswordHasherGateway
{
    /**
     * Hash un mot de passe en clair.
     *
     * @param string $plainPassword Le mot de passe à hasher
     *
     * @return HashedPassword Le mot de passe haché (validation intégrée)
     */
    public function hashPassword(string $plainPassword): HashedPassword;
}
