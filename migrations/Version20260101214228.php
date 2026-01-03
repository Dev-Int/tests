<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260101214228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Issue #196: Inverser hiérarchie packaging - consumerUnit obligatoire, parcel optionnel';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE packaging ALTER parcel_quantity DROP NOT NULL');
        $this->addSql('ALTER TABLE packaging ALTER consume_unit_quantity SET NOT NULL');
        $this->addSql('ALTER TABLE packaging ALTER parcel_unit_id DROP NOT NULL');
        $this->addSql('ALTER TABLE packaging ALTER consume_unit_unit_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE packaging ALTER consume_unit_quantity DROP NOT NULL');
        $this->addSql('ALTER TABLE packaging ALTER parcel_quantity SET NOT NULL');
        $this->addSql('ALTER TABLE packaging ALTER consume_unit_unit_id DROP NOT NULL');
        $this->addSql('ALTER TABLE packaging ALTER parcel_unit_id SET NOT NULL');
    }
}
