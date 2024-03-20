<?php

declare(strict_types=1);

/*
 * This file is part of the Tests package.
 *
 * (c) Dev-Int Création <info@developpement-interessant.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240320191933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add zoneStorage table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE zone_storage (' .
            'slug VARCHAR(255) NOT NULL, ' .
            'label VARCHAR(255) NOT NULL, ' .
            'familyLog_id UUID DEFAULT NULL, ' .
            'PRIMARY KEY(slug))'
        );
        $this->addSql('CREATE INDEX IDX_EA4E180C5B84275F ON zone_storage (familyLog_id)');
        $this->addSql(
            'ALTER TABLE zone_storage ADD CONSTRAINT FK_EA4E180C5B84275F FOREIGN KEY (familyLog_id) ' .
            'REFERENCES family_log (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('ALTER INDEX idx_494fd64636b7bdbb RENAME TO IDX_494FD646EC9C6612');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE zone_storage DROP CONSTRAINT FK_EA4E180C5B84275F');
        $this->addSql('DROP TABLE zone_storage');
        $this->addSql('ALTER INDEX idx_494fd646ec9c6612 RENAME TO idx_494fd64636b7bdbb');
    }
}
