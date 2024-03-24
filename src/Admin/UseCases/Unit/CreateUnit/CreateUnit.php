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

namespace Admin\UseCases\Unit\CreateUnit;

use Admin\Entities\Exception\UnitAlreadyExistsException;
use Admin\Entities\Unit\Unit;
use Admin\UseCases\Gateway\UnitRepository;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final readonly class CreateUnit
{
    public function __construct(private UnitRepository $unitRepository)
    {
    }

    public function execute(CreateUnitRequest $request): CreateUnitResponse
    {
        $uuid = ResourceUuid::generate();
        $isExists = $this->unitRepository->exists($request->label(), $uuid->toString());
        if ($isExists) {
            throw new UnitAlreadyExistsException($request->label());
        }

        $unit = Unit::create(
            $uuid,
            NameField::fromString($request->label()),
            $request->abbreviation()
        );

        $this->unitRepository->save($unit);

        return new CreateUnitResponse($unit);
    }
}
