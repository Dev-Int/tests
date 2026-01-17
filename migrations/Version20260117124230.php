<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260117124230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table for Auth BC with Symfony Security integration and soft-delete support';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE users (' .
            'uuid UUID NOT NULL, ' .
            'email VARCHAR(180) NOT NULL, ' .
            'password VARCHAR(255) NOT NULL, ' .
            'roles JSON NOT NULL, ' .
            'created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'disabled_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, ' .
            'PRIMARY KEY (uuid))'
        );
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON users (email)');
        $this->addSql('CREATE INDEX idx_user_disabled_at ON users (disabled_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
