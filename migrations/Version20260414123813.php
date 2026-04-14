<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add nullable position column to contact_person table.
 */
final class Version20260414123813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nullable position column to contact_person table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact_person ADD position VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact_person DROP position');
    }
}