<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260125170115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create employees table with indexes for email and disabled_at';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE employees (' .
            'uuid UUID NOT NULL, ' .
            'first_name VARCHAR(255) NOT NULL, ' .
            'last_name VARCHAR(255) NOT NULL, ' .
            'email VARCHAR(180) NOT NULL, ' .
            'phone VARCHAR(20) NOT NULL, ' .
            'position VARCHAR(255) NOT NULL, ' .
            'department VARCHAR(255) NOT NULL, ' .
            'hired_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'status VARCHAR(20) NOT NULL, ' .
            'user_uuid UUID NOT NULL, ' .
            'created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, ' .
            'disabled_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, ' .
            'PRIMARY KEY (uuid))'
        );
        $this->addSql('CREATE INDEX idx_employee_disabled_at ON employees (disabled_at)');
        $this->addSql('CREATE UNIQUE INDEX uniq_employee_email ON employees (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE employees');
    }
}
