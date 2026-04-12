<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Drop original_path column from media_item (image cropping removed)
 */
final class Version20260412100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop original_path column from media_item (image cropping removed)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item DROP original_path');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item ADD original_path VARCHAR(512) DEFAULT NULL');
    }
}