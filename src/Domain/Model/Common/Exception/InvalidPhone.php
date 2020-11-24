<?php

declare(strict_types=1);

namespace Domain\Model\Common\Exception;

final class InvalidPhone extends \DomainException
{
    /** @var string */
    protected $message = 'Le numéro saisie n\'est pas valide.';
}
