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

namespace Admin\UseCases\FamilyLog\ChangeParentFamilyLog;

use Admin\Entities\Exception\FamilyLogAlreadyExistsException;
use Admin\UseCases\Gateway\FamilyLogRepository;

final readonly class AssignParentFamilyLog
{
    public function __construct(private FamilyLogRepository $familyLogRepository)
    {
    }

    public function execute(AssignParentFamilyLogRequest $request): AssignParentFamilyLogResponse
    {
        $familyLog = $this->familyLogRepository->findBySlug($request->slug());

        $isExists = $this->familyLogRepository->exists($familyLog->label()->toString(), $request->parent());
        if ($isExists === true) {
            throw new FamilyLogAlreadyExistsException($familyLog->label()->toString());
        }

        $familyLog->assignParent($request->parent(), $familyLog->label());

        $this->familyLogRepository->assignParent($familyLog);

        return new AssignParentFamilyLogResponse($familyLog);
    }
}
