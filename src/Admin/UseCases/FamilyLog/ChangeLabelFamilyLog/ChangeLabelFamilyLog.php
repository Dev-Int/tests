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

namespace Admin\UseCases\FamilyLog\ChangeLabelFamilyLog;

use Admin\Entities\Exception\FamilyLogAlreadyExistsException;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final readonly class ChangeLabelFamilyLog
{
    public function __construct(private FamilyLogRepository $familyLogRepository)
    {
    }

    public function execute(ChangeLabelFamilyLogRequest $request): ChangeLabelFamilyLogResponse
    {
        $familyLog = $this->familyLogRepository->findByUuid(ResourceUuid::fromString($request->uuid()));

        $isExists = $this->familyLogRepository->exists(
            $request->label(),
            $familyLog->parent()
        );
        if ($isExists === true) {
            throw new FamilyLogAlreadyExistsException($request->label());
        }

        $familyLog->changeLabel(NameField::fromString($request->label()));

        $this->familyLogRepository->updateLabel($familyLog);

        return new ChangeLabelFamilyLogResponse($familyLog);
    }
}
