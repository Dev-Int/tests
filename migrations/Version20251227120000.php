<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251227120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add inventory_item_packaging table for storing packaging snapshots.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE inventory_item_packaging_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE inventory_item_packaging (' .
            'id INT NOT NULL, ' .
            'inventory_item_id INT NOT NULL, ' .
            'parcel_unit_label VARCHAR(50) NOT NULL, ' .
            'parcel_unit_abbreviation VARCHAR(10) NOT NULL, ' .
            'parcel_quantity DOUBLE PRECISION NOT NULL, ' .
            'sub_package_unit_label VARCHAR(50) DEFAULT NULL, ' .
            'sub_package_unit_abbreviation VARCHAR(10) DEFAULT NULL, ' .
            'sub_package_quantity DOUBLE PRECISION DEFAULT NULL, ' .
            'consumer_unit_label VARCHAR(50) DEFAULT NULL, ' .
            'consumer_unit_abbreviation VARCHAR(10) DEFAULT NULL, ' .
            'consumer_unit_quantity DOUBLE PRECISION DEFAULT NULL, ' .
            'PRIMARY KEY(id))'
        );
        $this->addSql(
            'ALTER TABLE inventory_item_packaging ADD CONSTRAINT FK_9E220B3536BF4A2 FOREIGN KEY (inventory_item_id) ' .
            'REFERENCES inventory_item (id) NOT DEFERRABLE'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9E220B3536BF4A2 ON inventory_item_packaging (inventory_item_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE inventory_item_packaging_id_seq CASCADE');
        $this->addSql('DROP INDEX UNIQ_9E220B3536BF4A2;');
        $this->addSql('ALTER TABLE inventory_item_packaging DROP CONSTRAINT FK_9E220B3536BF4A2');
        $this->addSql('DROP TABLE inventory_item_packaging');
    }
}
