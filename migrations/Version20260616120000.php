<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260616120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index for gallery image creation date filter';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_MEDIA_ITEM_IMAGE_CREATED_AT ON media_item (image_created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_MEDIA_ITEM_IMAGE_CREATED_AT ON media_item');
    }
}
