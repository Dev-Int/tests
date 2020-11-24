<?php

declare(strict_types=1);

namespace Domain\Model\Common\Exception;

final class InvalidQuantity extends \DomainException
{
    /** @var string */
    protected $message = 'La quantité doit être un nombre.';
}
