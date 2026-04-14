<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414123526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE department_training_session_location DROP FOREIGN KEY `FK_44CCC464D218E`');
        $this->addSql('ALTER TABLE department_training_session_location DROP FOREIGN KEY `FK_44CCC4951AC392`');
        $this->addSql('DROP TABLE department_training_session_location');
        $this->addSql('ALTER TABLE department_training_session ADD location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE department_training_session ADD CONSTRAINT FK_2A93503664D218E FOREIGN KEY (location_id) REFERENCES location (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2A93503664D218E ON department_training_session (location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE department_training_session_location (department_training_session_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_44CCC464D218E (location_id), INDEX IDX_44CCC4951AC392 (department_training_session_id), PRIMARY KEY (department_training_session_id, location_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE department_training_session_location ADD CONSTRAINT `FK_44CCC464D218E` FOREIGN KEY (location_id) REFERENCES location (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE department_training_session_location ADD CONSTRAINT `FK_44CCC4951AC392` FOREIGN KEY (department_training_session_id) REFERENCES department_training_session (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE department_training_session DROP FOREIGN KEY FK_2A93503664D218E');
        $this->addSql('DROP INDEX IDX_2A93503664D218E ON department_training_session');
        $this->addSql('ALTER TABLE department_training_session DROP location_id');
    }
}
