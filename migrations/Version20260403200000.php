<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create contact_person table for Kontaktpersonen.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contact_person (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, address LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_6C3C6515989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_person');
    }
}
