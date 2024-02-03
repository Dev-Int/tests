<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240125211114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Company table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE company ('.
            'slug VARCHAR(255) NOT NULL, '.
            'name VARCHAR(255) NOT NULL, '.
            'address VARCHAR(255) NOT NULL, '.
            'postal_code VARCHAR(255) NOT NULL, '.
            'town VARCHAR(255) NOT NULL, '.
            'country VARCHAR(255) NOT NULL, '.
            'phone VARCHAR(255) NOT NULL, '.
            'email VARCHAR(255) NOT NULL, '.
            'contact VARCHAR(255) NOT NULL, '.
            'PRIMARY KEY(slug))'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE company');
    }
}
