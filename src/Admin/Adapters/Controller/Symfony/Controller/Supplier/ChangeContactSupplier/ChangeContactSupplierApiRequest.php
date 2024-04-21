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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\ChangeContactSupplier;

use Admin\UseCases\Supplier\ChangeContactSupplier\ChangeContactSupplierRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangeContactSupplierApiRequest implements ChangeContactSupplierRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $contact,
        #[Assert\NotBlank]
        #[Assert\Regex('/^(?:0|\(?\+33\)?\s?|0033\s?)[1-79](?:[\.\-\s]?\d\d){4}$/')]
        public string $cellphone,
        #[Assert\NotBlank]
        public string $slug
    ) {
    }

    public function contact(): string
    {
        return $this->contact;
    }

    public function cellphone(): string
    {
        return $this->cellphone;
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
