<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260103183554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Issue #196: Aligner inventory_item_packaging - consumerUnit obligatoire, parcel optionnel';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER consumer_unit_label SET NOT NULL');
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER consumer_unit_abbreviation SET NOT NULL');
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER consumer_unit_quantity SET NOT NULL');
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER parcel_unit_label DROP NOT NULL');
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER parcel_unit_abbreviation DROP NOT NULL');
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER parcel_quantity DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER parcel_unit_label SET NOT NULL');
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER parcel_unit_abbreviation SET NOT NULL');
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER parcel_quantity SET NOT NULL');
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER consumer_unit_label DROP NOT NULL');
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER consumer_unit_abbreviation DROP NOT NULL');
        $this->addSql('ALTER TABLE inventory_item_packaging ALTER consumer_unit_quantity DROP NOT NULL');
    }
}
