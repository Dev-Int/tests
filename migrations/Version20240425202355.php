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

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240425202355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add article table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE article (' .
            'uuid UUID NOT NULL, ' .
            'supplier_id UUID DEFAULT NULL, ' .
            'tax_id UUID DEFAULT NULL, ' .
            'family_log_id UUID DEFAULT NULL, ' .
            'name VARCHAR(255) NOT NULL, ' .
            'packaging JSON NOT NULL, ' .
            'amount INT NOT NULL, ' .
            'min_stock DOUBLE PRECISION NOT NULL, ' .
            'quantity DOUBLE PRECISION NOT NULL, ' .
            'slug VARCHAR(255) NOT NULL, ' .
            'active BOOLEAN NOT NULL, ' .
            'PRIMARY KEY(uuid))'
        );
        $this->addSql('CREATE INDEX IDX_23A0E662ADD6D8C ON article (supplier_id)');
        $this->addSql('CREATE INDEX IDX_23A0E66B2A824D8 ON article (tax_id)');
        $this->addSql('CREATE INDEX IDX_23A0E6623570A93 ON article (family_log_id)');
        $this->addSql('COMMENT ON COLUMN article.packaging IS NULL');
        $this->addSql(
            'CREATE TABLE articles_zone_storages (' .
            'article_id UUID NOT NULL, ' .
            'zone_storage_id UUID NOT NULL, ' .
            'PRIMARY KEY(article_id, zone_storage_id))'
        );
        $this->addSql('CREATE INDEX IDX_D60996E47294869C ON articles_zone_storages (article_id)');
        $this->addSql('CREATE INDEX IDX_D60996E47C24C870 ON articles_zone_storages (zone_storage_id)');
        $this->addSql(
            'ALTER TABLE article ADD CONSTRAINT FK_23A0E662ADD6D8C FOREIGN KEY (supplier_id) ' .
            'REFERENCES supplier (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE article ADD CONSTRAINT FK_23A0E66B2A824D8 FOREIGN KEY (tax_id) ' .
            'REFERENCES tax (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE article ADD CONSTRAINT FK_23A0E6623570A93 FOREIGN KEY (family_log_id) ' .
            'REFERENCES family_log (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE articles_zone_storages ADD CONSTRAINT FK_D60996E47294869C FOREIGN KEY (article_id) ' .
            'REFERENCES article (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE articles_zone_storages ADD CONSTRAINT FK_D60996E47C24C870 FOREIGN KEY (zone_storage_id) ' .
            'REFERENCES zone_storage (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E662ADD6D8C');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E66B2A824D8');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E6623570A93');
        $this->addSql('ALTER TABLE articles_zone_storages DROP CONSTRAINT FK_D60996E47294869C');
        $this->addSql('ALTER TABLE articles_zone_storages DROP CONSTRAINT FK_D60996E47C24C870');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE articles_zone_storages');
    }
}
