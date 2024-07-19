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

final class Version20240712210900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add article table and its dependencies.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE packaging_seq_id INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE article (' .
            'uuid UUID NOT NULL, ' .
            'supplier_id UUID DEFAULT NULL, ' .
            'tax_id UUID DEFAULT NULL, ' .
            'family_log_id UUID DEFAULT NULL, ' .
            'name VARCHAR(255) NOT NULL, ' .
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
        $this->addSql(
            'CREATE TABLE articles_zone_storages (' .
            'article_id UUID NOT NULL, ' .
            'zone_storage_id UUID NOT NULL, ' .
            'PRIMARY KEY(article_id, zone_storage_id))'
        );
        $this->addSql('CREATE INDEX IDX_D60996E47294869C ON articles_zone_storages (article_id)');
        $this->addSql('CREATE INDEX IDX_D60996E47C24C870 ON articles_zone_storages (zone_storage_id)');

        $this->addSql(
            'CREATE TABLE packaging (' .
            'id SERIAL NOT NULL, ' .
            'article_id UUID NOT NULL,' .
            'parcel_unit_id UUID NOT NULL, ' .
            'parcel_quantity DOUBLE PRECISION NOT NULL, ' .
            'sub_package_unit_id UUID DEFAULT NULL, ' .
            'sub_package_quantity DOUBLE PRECISION DEFAULT NULL, ' .
            'consume_unit_unit_id UUID DEFAULT NULL, ' .
            'consume_unit_quantity DOUBLE PRECISION DEFAULT NULL, ' .
            'PRIMARY KEY(id))'
        );
        $this->addSql('ALTER TABLE packaging ALTER id DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF8B0D857294869C ON packaging (article_id)');
        $this->addSql('CREATE INDEX IDX_BF8B0D858DB8D596 ON packaging (parcel_unit_id)');
        $this->addSql('CREATE INDEX IDX_BF8B0D856D5D65AE ON packaging (sub_package_unit_id)');
        $this->addSql('CREATE INDEX IDX_BF8B0D85840987C6 ON packaging (consume_unit_unit_id)');
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
        $this->addSql(
            'ALTER TABLE packaging ADD CONSTRAINT FK_BF8B0D857294869C FOREIGN KEY (article_id) ' .
            'REFERENCES article (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql(
            'ALTER TABLE packaging ADD CONSTRAINT FK_BF8B0D858DB8D596 FOREIGN KEY (parcel_unit_id) ' .
            'REFERENCES unit (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE packaging ADD CONSTRAINT FK_BF8B0D856D5D65AE FOREIGN KEY (sub_package_unit_id) ' .
            'REFERENCES unit (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE packaging ADD CONSTRAINT FK_BF8B0D85840987C6 FOREIGN KEY (consume_unit_unit_id) ' .
            'REFERENCES unit (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE packaging_seq_id CASCADE');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E662ADD6D8C');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E66B2A824D8');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E6623570A93');
        $this->addSql('ALTER TABLE articles_zone_storages DROP CONSTRAINT FK_D60996E47294869C');
        $this->addSql('ALTER TABLE articles_zone_storages DROP CONSTRAINT FK_D60996E47C24C870');
        $this->addSql('ALTER TABLE packaging DROP CONSTRAINT FK_BF8B0D858DB8D596');
        $this->addSql('ALTER TABLE packaging DROP CONSTRAINT FK_BF8B0D856D5D65AE');
        $this->addSql('ALTER TABLE packaging DROP CONSTRAINT FK_BF8B0D85840987C6');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE articles_zone_storages');
        $this->addSql('DROP TABLE packaging');
    }
}
