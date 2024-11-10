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

final class Version20240416215741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add supplier table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE supplier (' .
            'uuid UUID NOT NULL, ' .
            'name VARCHAR(255) NOT NULL, ' .
            'address TEXT NOT NULL, ' .
            'postal_code VARCHAR(5) NOT NULL, ' .
            'city VARCHAR(255) NOT NULL, ' .
            'country VARCHAR(255) NOT NULL, ' .
            'phone VARCHAR(12) NOT NULL, ' .
            'email VARCHAR(255) NOT NULL, ' .
            'contact VARCHAR(100) NOT NULL, ' .
            'cellphone VARCHAR(12) NOT NULL, ' .
            'delay_delivery INT NOT NULL, ' .
            'order_days TEXT NOT NULL, ' .
            'active BOOLEAN NOT NULL, ' .
            'slug VARCHAR(255) NOT NULL, ' .
            'familyLog_id UUID DEFAULT NULL, ' .
            'PRIMARY KEY(uuid))'
        );
        $this->addSql('CREATE INDEX IDX_9B2A6C7E5B84275F ON supplier (familyLog_id)');
        $this->addSql('COMMENT ON COLUMN supplier.order_days IS \'(DC2Type:simple_array)\'');
        $this->addSql(
            'ALTER TABLE supplier ADD CONSTRAINT FK_9B2A6C7E5B84275F FOREIGN KEY (familyLog_id) ' .
            'REFERENCES family_log (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE supplier DROP CONSTRAINT FK_9B2A6C7E5B84275F');
        $this->addSql('DROP TABLE supplier');
    }
}
