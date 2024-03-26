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

final class Version20240325220255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Tax table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE tax (' .
            'uuid UUID NOT NULL, ' .
            'name VARCHAR(100) NOT NULL, ' .
            'rate DOUBLE PRECISION NOT NULL, ' .
            'PRIMARY KEY(uuid))'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tax');
    }
}
