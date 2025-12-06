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

final class Version20251206100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change order_days column type from simple_array (text) to json to preserve integer types.';
    }

    public function up(Schema $schema): void
    {
        // Convert existing simple_array data (comma-separated string) to JSON array
        $this->addSql(
            "UPDATE supplier SET order_days = CONCAT('[', REPLACE(order_days, ',', ','), ']') " .
            "WHERE order_days IS NOT NULL AND order_days != ''"
        );
        $this->addSql("UPDATE supplier SET order_days = '[]' WHERE order_days IS NULL OR order_days = ''");
        $this->addSql('ALTER TABLE supplier ALTER order_days TYPE JSON USING order_days::json');
        $this->addSql('COMMENT ON COLUMN supplier.order_days IS NULL');
    }

    public function down(Schema $schema): void
    {
        // Convert a JSON array back to simple_array (comma-separated string)
        $this->addSql(
            "UPDATE supplier SET order_days = TRIM(BOTH '[]' " .
            "FROM REPLACE(REPLACE(order_days::text, ' ', ''), ',', ',')) " .
            "WHERE order_days IS NOT NULL"
        );
        $this->addSql('ALTER TABLE supplier ALTER order_days TYPE TEXT');
    }
}
