<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251215194650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE packaging_id_seq CASCADE');
        $this->addSql(
            'CREATE TABLE inventory (' .
            'uuid UUID NOT NULL, ' .
            'zone_storage_ids JSON NOT NULL, ' .
            'date TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'status VARCHAR(255) NOT NULL, ' .
            'amount INT NOT NULL, ' .
            'PRIMARY KEY(uuid))'
        );
        $this->addSql('COMMENT ON COLUMN inventory.date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql(
            'CREATE TABLE inventory_item (' .
            'id SERIAL NOT NULL, ' .
            'inventory_id UUID NOT NULL, ' .
            'article_id UUID NOT NULL, ' .
            'price INT NOT NULL, ' .
            'theoretical_stock DOUBLE PRECISION NOT NULL, ' .
            'real_stock DOUBLE PRECISION NOT NULL, ' .
            'amount INT NOT NULL, ' .
            'PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_55BDEA309EEA759 ON inventory_item (inventory_id)');
        $this->addSql(
            'ALTER TABLE inventory_item ADD CONSTRAINT FK_55BDEA309EEA759 FOREIGN KEY (inventory_id) ' .
            'REFERENCES inventory (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE packaging_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE inventory_item DROP CONSTRAINT FK_55BDEA309EEA759');
        $this->addSql('DROP TABLE inventory');
        $this->addSql('DROP TABLE inventory_item');
    }
}
