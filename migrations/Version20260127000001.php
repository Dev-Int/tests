<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260127000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create password_reset_tokens table for password reset workflow';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE password_reset_tokens (' .
            'uuid UUID NOT NULL, ' .
            'user_uuid UUID NOT NULL, ' .
            'token VARCHAR(64) NOT NULL, ' .
            'created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'expires_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'used_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, ' .
            'PRIMARY KEY (uuid))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3967A2165F37A13B ON password_reset_tokens (token)');
        $this->addSql('CREATE INDEX idx_reset_expires_at ON password_reset_tokens (expires_at)');
        $this->addSql(
            'ALTER TABLE password_reset_tokens ADD CONSTRAINT FK_3967A216ABFE1C6F FOREIGN KEY (user_uuid) ' .
            'REFERENCES users (uuid) NOT DEFERRABLE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE password_reset_tokens');
    }
}
