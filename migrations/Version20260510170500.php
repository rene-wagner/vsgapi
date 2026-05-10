<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510170500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add department manager relation to contact person';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE department ADD manager_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A783E3463 FOREIGN KEY (manager_id) REFERENCES contact_person (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_CD1DE18A783E3463 ON department (manager_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A783E3463');
        $this->addSql('DROP INDEX IDX_CD1DE18A783E3463 ON department');
        $this->addSql('ALTER TABLE department DROP manager_id');
    }
}
