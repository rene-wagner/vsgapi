<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504121500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add league column to department_result';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE department_result ADD league VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE department_result DROP league');
    }
}
