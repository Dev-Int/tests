<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251215194650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add inventory table and its dependencies.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE inventory_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE inventory (' .
            'uuid UUID NOT NULL, ' .
            'zone_storage_ids JSON NOT NULL, ' .
            'date TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'status VARCHAR(255) NOT NULL, ' .
            'amount INT NOT NULL, ' .
            'created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'status_updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, ' .
            'PRIMARY KEY(uuid))'
        );
        $this->addSql(
            'CREATE TABLE inventory_item (' .
            'id INT NOT NULL, ' .
            'inventory_id UUID NOT NULL, ' .
            'article_id UUID NOT NULL, ' .
            'zone_storage_id UUID NOT NULL, ' .
            'article_name VARCHAR(255) NOT NULL, ' .
            'price INT NOT NULL, ' .
            'theoretical_stock INTEGER NOT NULL, ' .
            'real_stock INTEGER NOT NULL, ' .
            'amount INT NOT NULL, ' .
            'PRIMARY KEY(id))'
        );
        $this->addSql(
            'ALTER TABLE inventory_item ADD CONSTRAINT FK_55BDEA309EEA759 FOREIGN KEY (inventory_id) ' .
            'REFERENCES inventory (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('CREATE INDEX idx_inventory_item_inventory_id ON inventory_item (inventory_id)');
        $this->addSql('CREATE INDEX idx_inventory_item_article_zone ON inventory_item (article_id, zone_storage_id)');
        $this->addSql('CREATE INDEX idx_inventory_status ON inventory (status)');
        $this->addSql('CREATE INDEX idx_inventory_date ON inventory (date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE inventory_item_id_seq CASCADE');
        $this->addSql('DROP INDEX idx_inventory_date');
        $this->addSql('DROP INDEX idx_inventory_status');
        $this->addSql('DROP INDEX idx_inventory_item_article_zone');
        $this->addSql('DROP INDEX idx_inventory_item_inventory_id');
        $this->addSql('ALTER TABLE inventory_item DROP CONSTRAINT FK_55BDEA309EEA759');
        $this->addSql('DROP TABLE inventory_item');
        $this->addSql('DROP TABLE inventory');
    }
}
