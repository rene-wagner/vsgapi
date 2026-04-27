<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260427152028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_64C19C15E237E06 (name), UNIQUE INDEX UNIQ_64C19C1989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contact_person (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, position VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, address LONGTEXT DEFAULT NULL, is_board TINYINT DEFAULT 0 NOT NULL, picture_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_A44EE6F7989D9B62 (slug), INDEX IDX_A44EE6F7EE45BDBF (picture_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE content_block (pk INT AUTO_INCREMENT NOT NULL, id BINARY(16) NOT NULL, url VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY (pk)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, icon_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_CD1DE18A989D9B62 (slug), INDEX IDX_CD1DE18A54B9D732 (icon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE department_statistic (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, department_id INT NOT NULL, INDEX IDX_BF193150AE80F5DF (department_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE department_training_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, age_range VARCHAR(255) DEFAULT NULL, department_id INT NOT NULL, INDEX IDX_D86A806DAE80F5DF (department_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE department_training_session (id INT AUTO_INCREMENT NOT NULL, day VARCHAR(255) NOT NULL, time VARCHAR(255) NOT NULL, department_training_group_id INT NOT NULL, location_id INT DEFAULT NULL, INDEX IDX_2A935036B0AA8D96 (department_training_group_id), INDEX IDX_2A93503664D218E (location_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, location VARCHAR(255) DEFAULT NULL, recurrence VARCHAR(255) DEFAULT NULL, recurrence_until DATETIME DEFAULT NULL, picture_id INT DEFAULT NULL, INDEX IDX_3BAE0AA7EE45BDBF (picture_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, maps_url VARCHAR(2048) DEFAULT NULL, picture_id INT DEFAULT NULL, INDEX IDX_5E9E89CBEE45BDBF (picture_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE media_folder (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, parent_id INT DEFAULT NULL, INDEX IDX_50DB9313727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE media_item (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(127) NOT NULL, extension VARCHAR(16) NOT NULL, type VARCHAR(255) NOT NULL, size_bytes INT NOT NULL, path VARCHAR(512) NOT NULL, thumbnail_path VARCHAR(512) DEFAULT NULL, description LONGTEXT DEFAULT NULL, crop_x INT DEFAULT NULL, crop_y INT DEFAULT NULL, crop_width INT DEFAULT NULL, crop_height INT DEFAULT NULL, is_hidden_in_api TINYINT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, folder_id INT DEFAULT NULL, category_id INT DEFAULT NULL, INDEX IDX_DC5CFACD162CB942 (folder_id), INDEX IDX_DC5CFACD12469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, published TINYINT NOT NULL, hits INT DEFAULT 0 NOT NULL, old_post TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, author_id INT NOT NULL, picture_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_5A8A6C8D989D9B62 (slug), INDEX IDX_5A8A6C8DF675F31B (author_id), INDEX IDX_5A8A6C8DEE45BDBF (picture_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE post_category (post_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_B9A190604B89032C (post_id), INDEX IDX_B9A1906012469DE2 (category_id), PRIMARY KEY (post_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contact_person ADD CONSTRAINT FK_A44EE6F7EE45BDBF FOREIGN KEY (picture_id) REFERENCES media_item (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A54B9D732 FOREIGN KEY (icon_id) REFERENCES media_item (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE department_statistic ADD CONSTRAINT FK_BF193150AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE department_training_group ADD CONSTRAINT FK_D86A806DAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE department_training_session ADD CONSTRAINT FK_2A935036B0AA8D96 FOREIGN KEY (department_training_group_id) REFERENCES department_training_group (id)');
        $this->addSql('ALTER TABLE department_training_session ADD CONSTRAINT FK_2A93503664D218E FOREIGN KEY (location_id) REFERENCES location (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7EE45BDBF FOREIGN KEY (picture_id) REFERENCES media_item (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBEE45BDBF FOREIGN KEY (picture_id) REFERENCES media_item (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE media_folder ADD CONSTRAINT FK_50DB9313727ACA70 FOREIGN KEY (parent_id) REFERENCES media_folder (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE media_item ADD CONSTRAINT FK_DC5CFACD162CB942 FOREIGN KEY (folder_id) REFERENCES media_folder (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE media_item ADD CONSTRAINT FK_DC5CFACD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DEE45BDBF FOREIGN KEY (picture_id) REFERENCES media_item (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE post_category ADD CONSTRAINT FK_B9A190604B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_category ADD CONSTRAINT FK_B9A1906012469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_person DROP FOREIGN KEY FK_A44EE6F7EE45BDBF');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A54B9D732');
        $this->addSql('ALTER TABLE department_statistic DROP FOREIGN KEY FK_BF193150AE80F5DF');
        $this->addSql('ALTER TABLE department_training_group DROP FOREIGN KEY FK_D86A806DAE80F5DF');
        $this->addSql('ALTER TABLE department_training_session DROP FOREIGN KEY FK_2A935036B0AA8D96');
        $this->addSql('ALTER TABLE department_training_session DROP FOREIGN KEY FK_2A93503664D218E');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7EE45BDBF');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBEE45BDBF');
        $this->addSql('ALTER TABLE media_folder DROP FOREIGN KEY FK_50DB9313727ACA70');
        $this->addSql('ALTER TABLE media_item DROP FOREIGN KEY FK_DC5CFACD162CB942');
        $this->addSql('ALTER TABLE media_item DROP FOREIGN KEY FK_DC5CFACD12469DE2');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DEE45BDBF');
        $this->addSql('ALTER TABLE post_category DROP FOREIGN KEY FK_B9A190604B89032C');
        $this->addSql('ALTER TABLE post_category DROP FOREIGN KEY FK_B9A1906012469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE contact_person');
        $this->addSql('DROP TABLE content_block');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE department_statistic');
        $this->addSql('DROP TABLE department_training_group');
        $this->addSql('DROP TABLE department_training_session');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE media_folder');
        $this->addSql('DROP TABLE media_item');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_category');
        $this->addSql('DROP TABLE `user`');
    }
}
