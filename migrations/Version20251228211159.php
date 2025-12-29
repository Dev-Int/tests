<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251228211159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add real_stock_parcel, real_stock_sub_package, real_stock_consumer_unit columns to inventory_item for form prefill';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inventory_item ADD real_stock_parcel INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inventory_item ADD real_stock_sub_package INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inventory_item ADD real_stock_consumer_unit INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inventory_item DROP real_stock_parcel');
        $this->addSql('ALTER TABLE inventory_item DROP real_stock_sub_package');
        $this->addSql('ALTER TABLE inventory_item DROP real_stock_consumer_unit');
    }
}
