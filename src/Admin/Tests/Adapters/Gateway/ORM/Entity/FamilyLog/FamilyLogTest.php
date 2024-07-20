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

namespace Admin\Tests\Adapters\Gateway\ORM\Entity\FamilyLog;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Entities\FamilyLog\FamilyLog as FamilyLogDomain;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FamilyLogTest extends WebTestCase
{
    public function testFamilyLogORMToDomain(): void
    {
        // Arrange
        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        $familyLog0 = (new FamilyLogDataBuilder())->create('Alimentaire')->build();
        $familyLog1 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid('f016bde4-f36e-468b-bac0-af2b76a9d496')
            ->withParent($familyLog0)
            ->build()
        ;
        $familyLog2 = (new FamilyLogDataBuilder())->create('Viande')
            ->withUuid('4fb3318a-fdbd-4c8f-9937-4f5cb59e8352')
            ->withParent($familyLog1)
            ->build()
        ;
        $familyLogRepository->save($familyLog0);
        $familyLogRepository->save($familyLog1);
        $familyLogRepository->save($familyLog2);
        $familyLogORM2 = $familyLogRepository->find('4fb3318a-fdbd-4c8f-9937-4f5cb59e8352');
        static::assertInstanceOf(FamilyLog::class, $familyLogORM2);

        // Act
        $familyLogDomain2 = $familyLogORM2->toDomain($familyLogORM2->parent());

        // Assert
        static::assertEquals($familyLog2->uuid(), $familyLogDomain2->uuid());
        static::assertEquals($familyLog2->label(), $familyLogDomain2->label());
        static::assertEquals($familyLog2->slug(), $familyLogDomain2->slug());
        static::assertEquals($familyLog2->path(), $familyLogDomain2->path());
        static::assertEquals($familyLog2->level(), $familyLogDomain2->level());
        static::assertEquals($familyLog2->parent(), $familyLogDomain2->parent());
        static::assertEquals($familyLog2->children(), $familyLogDomain2->children());
        $parentDomain = $familyLogDomain2->parent();
        static::assertInstanceOf(FamilyLogDomain::class, $parentDomain);
        static::assertSame($familyLog1->uuid()->toString(), $parentDomain->uuid()->toString());
        static::assertSame($familyLog1->label()->toString(), $parentDomain->label()->toString());
        static::assertSame($familyLog1->slug(), $parentDomain->slug());
        static::assertSame($familyLog1->path(), $parentDomain->path());
        static::assertSame($familyLog1->level(), $parentDomain->level());
        static::assertNotEmpty($parentDomain->children());
        $grandParentDomain = $parentDomain->parent();
        static::assertInstanceOf(FamilyLogDomain::class, $grandParentDomain);
        static::assertSame($familyLog0->uuid()->toString(), $grandParentDomain->uuid()->toString());
        static::assertSame($familyLog0->label()->toString(), $grandParentDomain->label()->toString());
        static::assertSame($familyLog0->slug(), $grandParentDomain->slug());
        static::assertSame($familyLog0->path(), $grandParentDomain->path());
        static::assertSame($familyLog0->level(), $grandParentDomain->level());
        static::assertNotEmpty($grandParentDomain->children());
    }
}
