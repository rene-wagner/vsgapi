<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260605120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add persisted storage paths for media folders.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_folder ADD storage_path VARCHAR(512) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_folder DROP storage_path');
    }
}
