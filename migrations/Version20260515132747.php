<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260515132747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE club_history (id INT AUTO_INCREMENT NOT NULL, founding_date DATE NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE club_history_hall_of_fame_entry (id INT AUTO_INCREMENT NOT NULL, year INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, club_history_id INT NOT NULL, INDEX IDX_5AEE1D157C3CABF (club_history_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE club_history_membership_stat (id INT AUTO_INCREMENT NOT NULL, year INT NOT NULL, member_count INT NOT NULL, club_history_id INT NOT NULL, INDEX IDX_7B6E270D57C3CABF (club_history_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE club_history_milestone (id INT AUTO_INCREMENT NOT NULL, year INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, club_history_id INT NOT NULL, INDEX IDX_E89766757C3CABF (club_history_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE club_history_special_event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date DATE NOT NULL, description LONGTEXT DEFAULT NULL, club_history_id INT NOT NULL, INDEX IDX_2C16053657C3CABF (club_history_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE club_history_hall_of_fame_entry ADD CONSTRAINT FK_5AEE1D157C3CABF FOREIGN KEY (club_history_id) REFERENCES club_history (id)');
        $this->addSql('ALTER TABLE club_history_membership_stat ADD CONSTRAINT FK_7B6E270D57C3CABF FOREIGN KEY (club_history_id) REFERENCES club_history (id)');
        $this->addSql('ALTER TABLE club_history_milestone ADD CONSTRAINT FK_E89766757C3CABF FOREIGN KEY (club_history_id) REFERENCES club_history (id)');
        $this->addSql('ALTER TABLE club_history_special_event ADD CONSTRAINT FK_2C16053657C3CABF FOREIGN KEY (club_history_id) REFERENCES club_history (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club_history_hall_of_fame_entry DROP FOREIGN KEY FK_5AEE1D157C3CABF');
        $this->addSql('ALTER TABLE club_history_membership_stat DROP FOREIGN KEY FK_7B6E270D57C3CABF');
        $this->addSql('ALTER TABLE club_history_milestone DROP FOREIGN KEY FK_E89766757C3CABF');
        $this->addSql('ALTER TABLE club_history_special_event DROP FOREIGN KEY FK_2C16053657C3CABF');
        $this->addSql('DROP TABLE club_history');
        $this->addSql('DROP TABLE club_history_hall_of_fame_entry');
        $this->addSql('DROP TABLE club_history_membership_stat');
        $this->addSql('DROP TABLE club_history_milestone');
        $this->addSql('DROP TABLE club_history_special_event');
    }
}
