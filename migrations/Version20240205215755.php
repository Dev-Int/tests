<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240205215755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create family_log table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE family_log ('.
            'slug VARCHAR(255) NOT NULL, '.
            'label VARCHAR(64) NOT NULL, '.
            'path VARCHAR(3000) NOT NULL, '.
            'lvl INT NOT NULL, '.
            'parent_path VARCHAR(255) DEFAULT NULL, '.
            'PRIMARY KEY(slug))'
        );
        $this->addSql('CREATE INDEX IDX_494FD64636B7BDBB ON family_log (parent_path)');
        $this->addSql(
            'ALTER TABLE family_log ADD CONSTRAINT FK_494FD64636B7BDBB FOREIGN KEY '.
            '(parent_path) REFERENCES family_log (slug) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE family_log DROP CONSTRAINT FK_494FD64636B7BDBB');
        $this->addSql('DROP TABLE family_log');
    }
}
