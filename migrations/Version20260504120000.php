<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add department_result table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE department_result (id INT AUTO_INCREMENT NOT NULL, department_id INT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(2048) NOT NULL, INDEX IDX_51F478F9AE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE department_result ADD CONSTRAINT FK_51F478F9AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE department_result DROP FOREIGN KEY FK_51F478F9AE80F5DF');
        $this->addSql('DROP TABLE department_result');
    }
}
