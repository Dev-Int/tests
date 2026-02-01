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

namespace Admin\UseCases\Gateway;

use Shared\Entities\VO\EmailField;

final readonly class EmailPayload
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public EmailField $to,
        public EmailType $type,
        public string $subject,
        public array $context,
    ) {
    }
}
