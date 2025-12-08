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

namespace Admin\Tests\Story;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Entities\Company;
use Admin\Entities\Tax\Tax;
use Admin\Entities\Unit\Unit;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;

final readonly class InitialConfigurationStory
{
    public function __construct(
        private DoctrineCompanyRepository $companyRepository,
        private DoctrineUnitRepository $unitRepository,
        private DoctrineTaxRepository $taxRepository,
    ) {
    }

    /**
     * Crée une configuration minimale : Company + Unit + Tax.
     *
     * @return array{company: Company, unit: Unit, tax: Tax}
     */
    public function createMinimalConfiguration(
        string $companyName = 'Test Company',
        string $unitLabel = 'Kilogramme',
        string $unitAbbreviation = 'kg',
        string $taxName = 'TVA 20%',
        float $taxRate = 20.0,
    ): array {
        // 1. Company (premier obligatoire)
        $company = (new CompanyDataBuilder())->create($companyName)->build();
        $this->companyRepository->save($company);

        // 2. Unit
        $unit = (new UnitDataBuilder())
            ->create($unitLabel, $unitAbbreviation)
            ->build()
        ;
        $this->unitRepository->save($unit);

        // 3. Tax
        $tax = (new TaxDataBuilder())
            ->create($taxName, $taxRate)
            ->build()
        ;
        $this->taxRepository->save($tax);

        return [
            'company' => $company,
            'unit' => $unit,
            'tax' => $tax,
        ];
    }

    /**
     * Crée une configuration initiale sans Unit (Company + Tax seulement).
     *
     * @return array{company: Company, tax: Tax}
     */
    public function createCompanyAndTax(
        string $companyName = 'Test Company',
        string $taxName = 'TVA 20%',
        float $taxRate = 20.0,
    ): array {
        // 1. Company
        $company = (new CompanyDataBuilder())->create($companyName)->build();
        $this->companyRepository->save($company);

        // 2. Tax
        $tax = (new TaxDataBuilder())
            ->create($taxName, $taxRate)
            ->build()
        ;
        $this->taxRepository->save($tax);

        return [
            'company' => $company,
            'tax' => $tax,
        ];
    }

    /**
     * Crée plusieurs unités (kg, L, pce).
     *
     * @return array<Unit>
     */
    public function createMultipleUnits(): array
    {
        $units = [
            (new UnitDataBuilder())->create(label: 'Kilogramme', abbreviation: 'kg')->build(),
            (new UnitDataBuilder())->create(label: 'Litre', abbreviation: 'L')->build(),
            (new UnitDataBuilder())->create(label: 'Pièce', abbreviation: 'pce')->build(),
        ];

        foreach ($units as $unit) {
            $this->unitRepository->save($unit);
        }

        return $units;
    }

    /**
     * Crée plusieurs taxes (5.5%, 10%, 20%).
     *
     * @return array<Tax>
     */
    public function createMultipleTaxes(): array
    {
        $taxes = [
            (new TaxDataBuilder())->create(name: 'TVA 5.5%', rate: 5.5)->build(),
            (new TaxDataBuilder())->create(name: 'TVA 10%', rate: 10.0)->build(),
            (new TaxDataBuilder())->create(name: 'TVA 20%', rate: 20.0)->build(),
        ];

        foreach ($taxes as $tax) {
            $this->taxRepository->save($tax);
        }

        return $taxes;
    }

    /**
     * Crée les 4 taux de TVA français standards avec UUID constants.
     *
     * @return array{tauxNormal: Tax, tauxIntermediaire: Tax, tauxReduit: Tax, tauxParticulier: Tax}
     */
    public function createStandardTaxes(): array
    {
        $tauxNormal = (new TaxDataBuilder())
            ->create(name: 'TVA taux normal', rate: 20.0)
            ->withUuid(TaxDataBuilder::TAUX_NORMAL_UUID)
            ->build()
        ;
        $this->taxRepository->save($tauxNormal);

        $tauxIntermediaire = (new TaxDataBuilder())
            ->create(name: 'TVA taux intermédiaire', rate: 10.0)
            ->withUuid(TaxDataBuilder::TAUX_INTERMEDIAIRE_UUID)
            ->build()
        ;
        $this->taxRepository->save($tauxIntermediaire);

        $tauxReduit = (new TaxDataBuilder())
            ->create(name: 'TVA taux réduit', rate: 5.5)
            ->withUuid(TaxDataBuilder::TAUX_REDUIT_UUID)
            ->build()
        ;
        $this->taxRepository->save($tauxReduit);

        $tauxParticulier = (new TaxDataBuilder())
            ->create(name: 'TVA taux particulier', rate: 2.1)
            ->withUuid(TaxDataBuilder::TAUX_PARTICULIER_UUID)
            ->build()
        ;
        $this->taxRepository->save($tauxParticulier);

        return [
            'tauxNormal' => $tauxNormal,
            'tauxIntermediaire' => $tauxIntermediaire,
            'tauxReduit' => $tauxReduit,
            'tauxParticulier' => $tauxParticulier,
        ];
    }

    /**
     * Crée des unités pour le packaging d'articles (Colis, Pièce, Kilogramme).
     * Utilise des UUID constants définis dans UnitDataBuilder pour garantir la cohérence.
     *
     * @return array{colis: Unit, piece: Unit, kilogramme: Unit}
     */
    public function createPackagingUnits(
        string $colisLabel = 'Colis',
        string $colisAbbreviation = 'cls',
        string $pieceLabel = 'Pièce',
        string $pieceAbbreviation = 'pce',
        string $kilogrammeLabel = 'Kilogramme',
        string $kilogrammeAbbreviation = 'kg',
    ): array {
        $colis = (new UnitDataBuilder())
            ->create($colisLabel, $colisAbbreviation)
            ->withUuid(UnitDataBuilder::COLIS_UUID)
            ->build()
        ;
        $this->unitRepository->save($colis);

        $piece = (new UnitDataBuilder())
            ->create($pieceLabel, $pieceAbbreviation)
            ->withUuid(UnitDataBuilder::PIECE_UUID)
            ->build()
        ;
        $this->unitRepository->save($piece);

        $kilogramme = (new UnitDataBuilder())
            ->create($kilogrammeLabel, $kilogrammeAbbreviation)
            ->withUuid(UnitDataBuilder::KILOGRAMME_UUID)
            ->build()
        ;
        $this->unitRepository->save($kilogramme);

        return [
            'colis' => $colis,
            'piece' => $piece,
            'kilogramme' => $kilogramme,
        ];
    }

    /**
     * Crée les 6 unités de mesure standards avec UUID constants.
     *
     * @return array{colis: Unit, kilogramme: Unit, litre: Unit, piece: Unit, bouteille: Unit, boite: Unit}
     */
    public function createStandardUnits(): array
    {
        $colis = (new UnitDataBuilder())
            ->create('Colis', 'cls')
            ->withUuid(UnitDataBuilder::COLIS_UUID)
            ->build()
        ;
        $this->unitRepository->save($colis);

        $kilogramme = (new UnitDataBuilder())
            ->create('Kilogramme', 'kg')
            ->withUuid(UnitDataBuilder::KILOGRAMME_UUID)
            ->build()
        ;
        $this->unitRepository->save($kilogramme);

        $litre = (new UnitDataBuilder())
            ->create('Litre', 'l')
            ->withUuid(UnitDataBuilder::LITRE_UUID)
            ->build()
        ;
        $this->unitRepository->save($litre);

        $piece = (new UnitDataBuilder())
            ->create('Pièce', 'pce')
            ->withUuid(UnitDataBuilder::PIECE_UUID)
            ->build()
        ;
        $this->unitRepository->save($piece);

        $bouteille = (new UnitDataBuilder())
            ->create('Bouteille', 'btle')
            ->withUuid(UnitDataBuilder::BOUTEILLE_UUID)
            ->build()
        ;
        $this->unitRepository->save($bouteille);

        $boite = (new UnitDataBuilder())
            ->create('Boîte', 'bte')
            ->withUuid(UnitDataBuilder::BOITE_UUID)
            ->build()
        ;
        $this->unitRepository->save($boite);

        return [
            'colis' => $colis,
            'kilogramme' => $kilogramme,
            'litre' => $litre,
            'piece' => $piece,
            'bouteille' => $bouteille,
            'boite' => $boite,
        ];
    }
}
