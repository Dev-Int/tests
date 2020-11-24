<?php

declare(strict_types=1);

namespace Domain\Model\Common\Exception;

final class InvalidEmail extends \DomainException
{
    /** @var string */
    protected $message = 'L\'adresse mail saisie n\'est pas valide.';
}
