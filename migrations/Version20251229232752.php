<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251229232752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add discrepancy_amount column to inventory table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inventory ADD discrepancy_amount INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inventory DROP discrepancy_amount');
    }
}
