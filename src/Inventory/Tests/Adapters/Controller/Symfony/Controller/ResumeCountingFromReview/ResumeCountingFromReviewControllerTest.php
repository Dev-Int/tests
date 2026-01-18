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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\ResumeCountingFromReview;

use Admin\Tests\Factory\ZoneStorageFactory;
use Inventory\Adapters\Controller\Symfony\Controller\ResumeCountingFromReview\ResumeCountingFromReviewController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus as ORMInventoryStatus;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Tests\Factory\InventoryFactory;
use Inventory\Tests\Story\InventoryStory;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;
use Shared\Tests\BaseFunctionalTestCase;
use Shared\Tests\RedirectsToLoginTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\ResumeCountingFromReview\ResumeCountingFromReviewController
 */
final class ResumeCountingFromReviewControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    public const string RESUME_URI = '/inventories/%s/zones/%s/resume-counting';

    public function testResumeCountingFromReviewSuccessfully(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var InventoryRepository $repository */
        $repository = self::getContainer()->get(InventoryRepository::class);

        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneUuid = $zonePositive->_real()->uuid();

        $inventory = InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneUuid],
            'status' => ORMInventoryStatus::REVIEW->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Act
        $this->client->request(
            Request::METHOD_POST,
            \sprintf(self::RESUME_URI, $inventoryUuid, $zoneUuid)
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects(\sprintf('/inventories/%s/zones/%s/record', $inventoryUuid, $zoneUuid));

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-success')->text();
        self::assertSame($translator->trans('inventory.resume_counting.success'), $flash);

        $updatedInventory = $repository->getByUuid(ResourceUuid::fromString($inventoryUuid));
        self::assertTrue($updatedInventory->status()->equals(InventoryStatus::IN_PROGRESS));
    }

    public function testResumeCountingFailsOnInProgressStatus(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneUuid = $zonePositive->_real()->uuid();

        $inventory = InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneUuid],
            'status' => ORMInventoryStatus::IN_PROGRESS->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Act
        $this->client->request(
            Request::METHOD_POST,
            \sprintf(self::RESUME_URI, $inventoryUuid, $zoneUuid)
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects(\sprintf('/inventories/%s/review', $inventoryUuid));

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertSame($translator->trans('inventory.resume_counting.errors.cannot_resume'), $flash);
    }

    public function testResumeCountingFailsOnCompletedStatus(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneUuid = $zonePositive->_real()->uuid();

        $inventory = InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneUuid],
            'status' => ORMInventoryStatus::COMPLETED->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Act
        $this->client->request(
            Request::METHOD_POST,
            \sprintf(self::RESUME_URI, $inventoryUuid, $zoneUuid)
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertSame($translator->trans('inventory.resume_counting.errors.cannot_resume'), $flash);
    }

    public function testResumeCountingFailsOnInventoryNotFound(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        InventoryStory::load();

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneUuid = $zonePositive->_real()->uuid();
        $nonExistentUuid = '00000000-0000-0000-0000-000000000000';

        // Act
        $this->client->request(
            Request::METHOD_POST,
            \sprintf(self::RESUME_URI, $nonExistentUuid, $zoneUuid)
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories/');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertSame($translator->trans('inventory.errors.not_found'), $flash);
    }

    public function testRouteNameConstantExists(): void
    {
        self::assertTrue(\defined(ResumeCountingFromReviewController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_resume_counting', ResumeCountingFromReviewController::ROUTE_NAME);
    }

    public function testAccessDeniedForRoleUser(): void
    {
        $this->logoutUser();
        $this->authenticateAsRoleUser();
        $this->client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access Denied.');
        $this->client->request(Request::METHOD_POST, $this->getProtectedUri());
    }

    protected function getProtectedUri(): string
    {
        // UUIDs factices, access_control vérifie l'auth avant le routage complet
        return \sprintf(
            self::RESUME_URI,
            '00000000-0000-0000-0000-000000000000',
            '00000000-0000-0000-0000-000000000001'
        );
    }

    protected function getProtectedHttpMethod(): string
    {
        return Request::METHOD_POST;
    }
}
