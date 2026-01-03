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

namespace Admin\UseCases\Article\ChangeStorageInformation;

use Admin\Entities\Unit\Unit;
use Shared\Entities\ResourceUuid;

interface ChangeArticleStorageInformationRequest
{
    /**
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     *                                                                                     [consumerUnit, subPackage, parcel]
     */
    public function packaging(): array;

    public function minStock(): float;

    public function uuid(): ResourceUuid;
}
