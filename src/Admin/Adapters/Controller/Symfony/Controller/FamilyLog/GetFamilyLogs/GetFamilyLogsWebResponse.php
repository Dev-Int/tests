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

namespace Admin\Adapters\Controller\Symfony\Controller\FamilyLog\GetFamilyLogs;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\UseCases\FamilyLog\GetFamilyLogs\GetFamilyLogsResponse;

final class GetFamilyLogsWebResponse
{
    /** @var array<FamilyLogDto> */
    private array $familyLogs = [];

    public function __construct(GetFamilyLogsResponse $familyLogs)
    {
        foreach ($familyLogs->familyLogs as $familyLog) {
            $this->familyLogs[] = new FamilyLogDto(
                (new FamilyLog())->fromDomain($familyLog)->getIndentedLabel(),
                $familyLog->slug()
            );
        }
    }

    /**
     * @return array<FamilyLogDto>
     */
    public function familyLogs(): array
    {
        return $this->familyLogs;
    }
}
