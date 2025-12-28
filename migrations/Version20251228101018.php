<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251228101018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reviewed, review_notes and action_plan columns to inventory_item table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inventory_item ADD reviewed BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE inventory_item ADD review_notes TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE inventory_item ADD action_plan TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inventory_item DROP reviewed');
        $this->addSql('ALTER TABLE inventory_item DROP review_notes');
        $this->addSql('ALTER TABLE inventory_item DROP action_plan');
    }
}
