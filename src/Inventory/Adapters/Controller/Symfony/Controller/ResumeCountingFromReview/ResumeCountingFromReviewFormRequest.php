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

namespace Inventory\Adapters\Controller\Symfony\Controller\ResumeCountingFromReview;

use Inventory\UseCases\ResumeCountingFromReview\ResumeCountingFromReviewRequest;
use Shared\Entities\ResourceUuid;

final readonly class ResumeCountingFromReviewFormRequest implements ResumeCountingFromReviewRequest
{
    public function __construct(
        private ResourceUuid $inventoryUuid,
        private ResourceUuid $zoneStorageUuid,
    ) {
    }

    public function inventoryUuid(): ResourceUuid
    {
        return $this->inventoryUuid;
    }

    public function zoneStorageUuid(): ResourceUuid
    {
        return $this->zoneStorageUuid;
    }
}
