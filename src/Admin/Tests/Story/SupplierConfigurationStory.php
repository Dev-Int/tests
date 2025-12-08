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

use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Faker\Factory;

final class SupplierConfigurationStory
{
    public function __construct(
        private readonly DoctrineSupplierRepository $supplierRepository,
    ) {
    }

    /**
     * Crée un Supplier simple.
     */
    public function createSupplier(
        string $name,
        FamilyLog $familyLog,
        ?string $uuid = null,
    ): Supplier {
        $builder = (new SupplierDataBuilder())->create($name, $familyLog);

        if (null !== $uuid) {
            $builder->withUuid($uuid);
        }

        $supplier = $builder->build();

        $this->supplierRepository->save($supplier);

        return $supplier;
    }

    /**
     * Crée un Supplier avec adresse complète.
     */
    public function createSupplierWithFullAddress(
        string $name,
        FamilyLog $familyLog,
        string $address = '12 rue des Lilas',
        string $postalCode = '75001',
        string $town = 'Paris',
        string $email = 'contact@supplier.fr',
        string $phone = '0123456789',
        string $contact = 'Responsable',
    ): Supplier {
        $supplier = (new SupplierDataBuilder())
            ->create($name, $familyLog)
            ->withAddress($address)
            ->withPostalCode($postalCode)
            ->withTown($town)
            ->withEmail($email)
            ->withPhone($phone)
            ->withContact($contact)
            ->build()
        ;

        $this->supplierRepository->save($supplier);

        return $supplier;
    }

    /**
     * Crée plusieurs Suppliers pour une même FamilyLog.
     *
     * @param array<string> $supplierNames
     *
     * @return array<Supplier>
     */
    public function createMultipleSuppliersForFamily(
        FamilyLog $familyLog,
        array $supplierNames = ['Fournisseur A', 'Fournisseur B', 'Fournisseur C'],
    ): array {
        $suppliers = [];
        foreach ($supplierNames as $name) {
            $suppliers[] = $this->createSupplier($name, $familyLog);
        }

        return $suppliers;
    }

    /**
     * Crée les 4 Suppliers standards basés sur les fixtures avec des données réalistes générées par Faker.
     * Cette méthode nécessite que les FamilyLog correspondantes existent déjà.
     *
     * @param array{
     *     surgele: FamilyLog,
     *     frais: FamilyLog,
     *     epicerie: FamilyLog,
     *     fraisFruitsLegumes: FamilyLog
     * } $familyLogs
     *
     * @return array{
     *     supplierSurgele: Supplier,
     *     supplierFrais: Supplier,
     *     supplierEpicerie: Supplier,
     *     supplierMaraichere: Supplier
     * }
     */
    public function createStandardSuppliers(array $familyLogs): array
    {
        $faker = Factory::create('fr_FR');

        $supplierSurgele = (new SupplierDataBuilder())
            ->create($faker->company(), $familyLogs['surgele'])
            ->withUuid(SupplierDataBuilder::SUPPLIER_SURGELE_UUID)
            ->withAddress($faker->streetAddress())
            ->withPostalCode($faker->postcode())
            ->withTown($faker->city())
            ->withPhone($faker->phoneNumber())
            ->withCellphone($faker->phoneNumber())
            ->withEmail($faker->email())
            ->withContact($faker->name())
            ->withDelayDelivery(2)
            ->withOrderDays([1, 4])
            ->build()
        ;
        $this->supplierRepository->save($supplierSurgele);

        $supplierFrais = (new SupplierDataBuilder())
            ->create($faker->company(), $familyLogs['frais'])
            ->withUuid(SupplierDataBuilder::SUPPLIER_FRAIS_UUID)
            ->withAddress($faker->streetAddress())
            ->withPostalCode($faker->postcode())
            ->withTown($faker->city())
            ->withPhone($faker->phoneNumber())
            ->withCellphone($faker->phoneNumber())
            ->withEmail($faker->email())
            ->withContact($faker->name())
            ->withDelayDelivery(3)
            ->withOrderDays([2, 5])
            ->build()
        ;
        $this->supplierRepository->save($supplierFrais);

        $supplierEpicerie = (new SupplierDataBuilder())
            ->create($faker->company(), $familyLogs['epicerie'])
            ->withUuid(SupplierDataBuilder::SUPPLIER_EPICERIE_UUID)
            ->withAddress($faker->streetAddress())
            ->withPostalCode($faker->postcode())
            ->withTown($faker->city())
            ->withPhone($faker->phoneNumber())
            ->withCellphone($faker->phoneNumber())
            ->withEmail($faker->email())
            ->withContact($faker->name())
            ->withDelayDelivery(4)
            ->withOrderDays([4])
            ->build()
        ;
        $this->supplierRepository->save($supplierEpicerie);

        $supplierMaraichere = (new SupplierDataBuilder())
            ->create($faker->company(), $familyLogs['fraisFruitsLegumes'])
            ->withUuid(SupplierDataBuilder::SUPPLIER_MARAICHERE_UUID)
            ->withAddress($faker->streetAddress())
            ->withPostalCode($faker->postcode())
            ->withTown($faker->city())
            ->withPhone($faker->phoneNumber())
            ->withCellphone($faker->phoneNumber())
            ->withEmail($faker->email())
            ->withContact($faker->name())
            ->withDelayDelivery(1)
            ->withOrderDays([1, 3, 5])
            ->build()
        ;
        $this->supplierRepository->save($supplierMaraichere);

        return [
            'supplierSurgele' => $supplierSurgele,
            'supplierFrais' => $supplierFrais,
            'supplierEpicerie' => $supplierEpicerie,
            'supplierMaraichere' => $supplierMaraichere,
        ];
    }
}
