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

final class Version20240323212044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Unit table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE unit (' .
            'slug VARCHAR(50) NOT NULL, ' .
            'label VARCHAR(50) NOT NULL, ' .
            'abbreviation VARCHAR(5) NOT NULL, ' .
            'PRIMARY KEY(slug))'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE unit');
    }
}
