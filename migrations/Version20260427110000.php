<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260427110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_board to contact_person';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->getTable('contact_person')->hasColumn('is_board')) {
            $this->addSql('ALTER TABLE contact_person ADD is_board TINYINT(1) NOT NULL DEFAULT 0');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->getTable('contact_person')->hasColumn('is_board')) {
            $this->addSql('ALTER TABLE contact_person DROP is_board');
        }
    }
}
