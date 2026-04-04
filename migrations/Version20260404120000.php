<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260404120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create media_folder and media_item tables for Mediathek.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE media_folder (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5C2FA36B727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_item (id INT AUTO_INCREMENT NOT NULL, folder_id INT DEFAULT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(127) NOT NULL, extension VARCHAR(16) NOT NULL, type VARCHAR(255) NOT NULL, size_bytes INT NOT NULL, path VARCHAR(512) NOT NULL, thumbnail_path VARCHAR(512) DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_ADBDEA8F162CB942 (folder_id), INDEX IDX_ADBDEA8F12469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE media_folder ADD CONSTRAINT FK_5C2FA36B727ACA70 FOREIGN KEY (parent_id) REFERENCES media_folder (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE media_item ADD CONSTRAINT FK_ADBDEA8F162CB942 FOREIGN KEY (folder_id) REFERENCES media_folder (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE media_item ADD CONSTRAINT FK_ADBDEA8F12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_item DROP FOREIGN KEY FK_ADBDEA8F12469DE2');
        $this->addSql('ALTER TABLE media_item DROP FOREIGN KEY FK_ADBDEA8F162CB942');
        $this->addSql('ALTER TABLE media_folder DROP FOREIGN KEY FK_5C2FA36B727ACA70');
        $this->addSql('DROP TABLE media_item');
        $this->addSql('DROP TABLE media_folder');
    }
}
