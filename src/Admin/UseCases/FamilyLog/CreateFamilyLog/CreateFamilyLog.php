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

namespace Admin\UseCases\FamilyLog\CreateFamilyLog;

use Admin\Entities\Exception\FamilyLogAlreadyExistsException;
use Admin\Entities\FamilyLog;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final readonly class CreateFamilyLog
{
    public function __construct(private FamilyLogRepository $familyLogRepository)
    {
    }

    public function execute(CreateFamilyLogRequest $request): CreateFamilyLogResponse
    {
        $isExists = $this->familyLogRepository->exists($request->label(), $request->parent());
        if ($isExists === true) {
            throw new FamilyLogAlreadyExistsException($request->label());
        }

        $familyLog = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString($request->label()),
            $request->parent()
        );

        $this->familyLogRepository->save($familyLog);

        return new CreateFamilyLogResponse($familyLog);
    }
}
