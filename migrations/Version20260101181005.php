<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101181005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename address column to street_address to avoid Symfony PropertyAccessor bug (interprets "address" as "add" + "ress")';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company RENAME COLUMN address TO street_address');
        $this->addSql('ALTER TABLE supplier RENAME COLUMN address TO street_address');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company RENAME COLUMN street_address TO address');
        $this->addSql('ALTER TABLE supplier RENAME COLUMN street_address TO address');
    }
}
