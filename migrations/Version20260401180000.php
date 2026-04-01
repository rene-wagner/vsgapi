<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260401180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create post table, post_category join table, and author relation to user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, published TINYINT(1) NOT NULL, hits INT DEFAULT 0 NOT NULL, old_post TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', author_id INT NOT NULL, UNIQUE INDEX UNIQ_5A8A6C8D989D9B62 (slug), INDEX IDX_5A8A6C8DF675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_category (post_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_B9C1903B4B89032C (post_id), INDEX IDX_B9C1903B12469DE2 (category_id), PRIMARY KEY (post_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE post_category ADD CONSTRAINT FK_B9C1903B4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_category ADD CONSTRAINT FK_B9C1903B12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post_category DROP FOREIGN KEY FK_B9C1903B12469DE2');
        $this->addSql('ALTER TABLE post_category DROP FOREIGN KEY FK_B9C1903B4B89032C');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B');
        $this->addSql('DROP TABLE post_category');
        $this->addSql('DROP TABLE post');
    }
}
