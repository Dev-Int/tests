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

namespace Auth\Contracts\Services\CommandHandler\UpdateUser;

use Shared\Entities\VO\EmailField;

final readonly class UpdateUserCommand
{
    /**
     * @param array<string>|null $roles
     */
    public function __construct(
        public string $uuid,
        public ?EmailField $email = null,
        public ?string $plainPassword = null,
        public ?array $roles = null,
    ) {
    }
}
