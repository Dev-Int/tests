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

namespace Auth\Entities\VO;

use Auth\Entities\Exception\InvalidHashedPassword;

/**
 * Value Object encapsulating a hashed password.
 *
 * This VO stores the already-hashed password string.
 * Hashing is done in the adapter layer (Symfony PasswordHasher).
 */
final readonly class HashedPassword
{
    /**
     * Regex pattern for valid hash algorithms.
     * - bcrypt: $2y$, $2a$, $2b$
     * - argon2i/argon2id: $argon2i$, $argon2id$
     * - scrypt: $scrypt$.
     */
    private const string HASH_PATTERN = '/^\$(2[ayb]|argon2i(d)?|scrypt)\$/';

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    private function __construct(private string $hash)
    {
        if ($hash === '' || preg_match(self::HASH_PATTERN, $hash) !== 1) {
            throw new InvalidHashedPassword();
        }
    }

    public function toString(): string
    {
        return $this->hash;
    }

    public function equals(self $other): bool
    {
        return $this->hash === $other->hash;
    }
}
