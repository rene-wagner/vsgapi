<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Add crop metadata columns to media_item
 */
final class Version20260413120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add crop_x, crop_y, crop_width, crop_height columns to media_item';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item ADD crop_x INT DEFAULT NULL, ADD crop_y INT DEFAULT NULL, ADD crop_width INT DEFAULT NULL, ADD crop_height INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item DROP crop_x, DROP crop_y, DROP crop_width, DROP crop_height');
    }
}