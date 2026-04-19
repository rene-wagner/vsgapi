<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_hidden_in_api column to media_item table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item ADD is_hidden_in_api TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item DROP is_hidden_in_api');
    }
}