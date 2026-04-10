<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408085413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_person ADD picture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_person ADD CONSTRAINT FK_A44EE6F7EE45BDBF FOREIGN KEY (picture_id) REFERENCES media_item (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_A44EE6F7EE45BDBF ON contact_person (picture_id)');
        $this->addSql('ALTER TABLE contact_person RENAME INDEX uniq_6c3c6515989d9b62 TO UNIQ_A44EE6F7989D9B62');
        $this->addSql('ALTER TABLE department ADD icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A54B9D732 FOREIGN KEY (icon_id) REFERENCES media_item (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_CD1DE18A54B9D732 ON department (icon_id)');
        $this->addSql('ALTER TABLE location ADD picture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBEE45BDBF FOREIGN KEY (picture_id) REFERENCES media_item (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_5E9E89CBEE45BDBF ON location (picture_id)');
        $this->addSql('ALTER TABLE media_folder CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE media_folder RENAME INDEX idx_5c2fa36b727aca70 TO IDX_50DB9313727ACA70');
        $this->addSql('ALTER TABLE media_item CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE media_item RENAME INDEX idx_adbdea8f162cb942 TO IDX_DC5CFACD162CB942');
        $this->addSql('ALTER TABLE media_item RENAME INDEX idx_adbdea8f12469de2 TO IDX_DC5CFACD12469DE2');
        $this->addSql('ALTER TABLE post ADD picture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DEE45BDBF FOREIGN KEY (picture_id) REFERENCES media_item (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DEE45BDBF ON post (picture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_person DROP FOREIGN KEY FK_A44EE6F7EE45BDBF');
        $this->addSql('DROP INDEX IDX_A44EE6F7EE45BDBF ON contact_person');
        $this->addSql('ALTER TABLE contact_person DROP picture_id');
        $this->addSql('ALTER TABLE contact_person RENAME INDEX uniq_a44ee6f7989d9b62 TO UNIQ_6C3C6515989D9B62');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A54B9D732');
        $this->addSql('DROP INDEX IDX_CD1DE18A54B9D732 ON department');
        $this->addSql('ALTER TABLE department DROP icon_id');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBEE45BDBF');
        $this->addSql('DROP INDEX IDX_5E9E89CBEE45BDBF ON location');
        $this->addSql('ALTER TABLE location DROP picture_id');
        $this->addSql('ALTER TABLE media_folder CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE media_folder RENAME INDEX idx_50db9313727aca70 TO IDX_5C2FA36B727ACA70');
        $this->addSql('ALTER TABLE media_item CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE media_item RENAME INDEX idx_dc5cfacd162cb942 TO IDX_ADBDEA8F162CB942');
        $this->addSql('ALTER TABLE media_item RENAME INDEX idx_dc5cfacd12469de2 TO IDX_ADBDEA8F12469DE2');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DEE45BDBF');
        $this->addSql('DROP INDEX IDX_5A8A6C8DEE45BDBF ON post');
        $this->addSql('ALTER TABLE post DROP picture_id');
    }
}
