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

namespace Admin\UseCases\FamilyLog\GetFamilyLogs;

use Admin\Entities\Repository\FamilyLogRepository;

final readonly class GetFamilyLogs
{
    public function __construct(private FamilyLogRepository $repository)
    {
    }

    public function execute(): GetFamilyLogsResponse
    {
        $familyLogs = $this->repository->getFamilyLogsOrderingBySlug();

        return new GetFamilyLogsResponse($familyLogs);
    }
}
