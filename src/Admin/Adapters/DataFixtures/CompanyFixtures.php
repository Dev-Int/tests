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

namespace Admin\Adapters\DataFixtures;

use Admin\Adapters\Gateway\ORM\Entity\Company;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class CompanyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $company = (new CompanyDataBuilder())->create('Dev-Int Création')
            ->build()
        ;

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = $manager->getRepository(Company::class);
        $companyRepository->save($company);

        $manager->flush();
    }
}
