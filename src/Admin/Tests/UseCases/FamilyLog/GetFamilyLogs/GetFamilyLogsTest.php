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

namespace Admin\Tests\UseCases\FamilyLog\GetFamilyLogs;

use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\FamilyLog\FamilyLogCollection;
use Admin\Entities\Repository\FamilyLogRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\UseCases\FamilyLog\GetFamilyLogs\GetFamilyLogs;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class GetFamilyLogsTest extends TestCase
{
    public function testGetFamilyLogsWithSuccess(): void
    {
        // Arrange
        $familyLogRepository = $this->createMock(FamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $familyLog1 = $familyLogBuilder->create('Frais')->build();
        $familyLog2 = $familyLogBuilder->create('Surgelé')
            ->withUuid('1bd26d49-ed55-4754-94dd-895beda11c6b')
            ->build()
        ;
        $familyLog3 = $familyLogBuilder->create('Viande')
            ->withUuid('a3454c1b-083f-4a84-9d88-137a44b9a5ab')
            ->withParent($familyLog1)
            ->build()
        ;
        $familyLogsArray = [$familyLog1, $familyLog2, $familyLog3];
        usort($familyLogsArray, static function (FamilyLog $a, FamilyLog $b) {
            return $a->slug() <=> $b->slug();
        });
        $familyLogs = new FamilyLogCollection();
        foreach ($familyLogsArray as $familyLog) {
            $familyLogs->add($familyLog);
        }

        $familyLogRepository->expects(self::once())
            ->method('getFamilyLogsOrderingBySlug')
            ->willReturn($familyLogs)
        ;

        $useCase = new GetFamilyLogs($familyLogRepository);

        // Act
        $response = $useCase->execute();
        $getFamilyLogs = $response->familyLogs;

        // Assert
        self::assertCount(3, $getFamilyLogs);
        $getFamilyLog1 = $getFamilyLogs->current();
        self::assertSame('Frais', $getFamilyLog1->label()->toString());
        $getFamilyLogs->next();
        $getFamilyLog2 = $getFamilyLogs->current();
        self::assertSame('Viande', $getFamilyLog2->label()->toString());
        self::assertSame('Frais', $getFamilyLog2->parent()?->label()->toString());
        $getFamilyLogs->next();
        $getFamilyLog3 = $getFamilyLogs->current();
        self::assertSame('Surgelé', $getFamilyLog3->label()->toString());
    }
}
