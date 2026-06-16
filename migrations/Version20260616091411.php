<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260616091411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image creation date to media items';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item ADD image_created_at DATETIME DEFAULT NULL');
        $this->addSql("UPDATE media_item SET image_created_at = created_at WHERE type = 'image' AND image_created_at IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item DROP image_created_at');
    }
}
