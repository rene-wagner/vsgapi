<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260401200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create department, statistics, training groups/sessions, and session-location join.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_CD1DE18A989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department_statistic (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, department_id INT NOT NULL, INDEX IDX_BF193150AE80F5DF (department_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department_training_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, age_range VARCHAR(255) NOT NULL, department_id INT NOT NULL, INDEX IDX_D86A806DAE80F5DF (department_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department_training_session (id INT AUTO_INCREMENT NOT NULL, day VARCHAR(255) NOT NULL, time VARCHAR(255) NOT NULL, department_training_group_id INT NOT NULL, INDEX IDX_2A935036B0AA8D96 (department_training_group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department_training_session_location (department_training_session_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_44CCC4951AC392 (department_training_session_id), INDEX IDX_44CCC464D218E (location_id), PRIMARY KEY (department_training_session_id, location_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE department_statistic ADD CONSTRAINT FK_BF193150AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE department_training_group ADD CONSTRAINT FK_D86A806DAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE department_training_session ADD CONSTRAINT FK_2A935036B0AA8D96 FOREIGN KEY (department_training_group_id) REFERENCES department_training_group (id)');
        $this->addSql('ALTER TABLE department_training_session_location ADD CONSTRAINT FK_44CCC4951AC392 FOREIGN KEY (department_training_session_id) REFERENCES department_training_session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE department_training_session_location ADD CONSTRAINT FK_44CCC464D218E FOREIGN KEY (location_id) REFERENCES location (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE department_training_session_location DROP FOREIGN KEY FK_44CCC4951AC392');
        $this->addSql('ALTER TABLE department_training_session_location DROP FOREIGN KEY FK_44CCC464D218E');
        $this->addSql('ALTER TABLE department_training_session DROP FOREIGN KEY FK_2A935036B0AA8D96');
        $this->addSql('ALTER TABLE department_statistic DROP FOREIGN KEY FK_BF193150AE80F5DF');
        $this->addSql('ALTER TABLE department_training_group DROP FOREIGN KEY FK_D86A806DAE80F5DF');
        $this->addSql('DROP TABLE department_training_session_location');
        $this->addSql('DROP TABLE department_training_session');
        $this->addSql('DROP TABLE department_training_group');
        $this->addSql('DROP TABLE department_statistic');
        $this->addSql('DROP TABLE department');
    }
}
