<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260406120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add media_item.original_path for unmodified image backup before crop.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item ADD original_path VARCHAR(512) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item DROP original_path');
    }
}
