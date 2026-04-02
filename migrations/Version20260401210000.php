<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260401210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make department_training_group.age_range nullable (optional).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE department_training_group CHANGE age_range age_range VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE department_training_group SET age_range = \'\' WHERE age_range IS NULL');
        $this->addSql('ALTER TABLE department_training_group CHANGE age_range age_range VARCHAR(255) NOT NULL');
    }
}
