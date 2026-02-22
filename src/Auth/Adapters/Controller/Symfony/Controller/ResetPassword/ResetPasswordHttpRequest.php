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

namespace Auth\Adapters\Controller\Symfony\Controller\ResetPassword;

use Auth\Entities\ResetPassword;
use Auth\UseCases\ResetPassword\ResetPasswordRequest;

final readonly class ResetPasswordHttpRequest implements ResetPasswordRequest
{
    public function __construct(
        public ResetPassword $token,
        public string $plainPassword
    ) {
    }

    public function token(): ResetPassword
    {
        return $this->token;
    }

    public function plainPassword(): string
    {
        return $this->plainPassword;
    }
}
