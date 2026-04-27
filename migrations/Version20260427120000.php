<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260427120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change content_block to technical primary key and unique id+url';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('content_block')) {
            return;
        }

        $table = $schema->getTable('content_block');

        if (!$table->hasColumn('pk')) {
            $this->addSql('ALTER TABLE content_block DROP PRIMARY KEY, ADD pk INT AUTO_INCREMENT NOT NULL PRIMARY KEY FIRST');
        }

        if (!$table->hasIndex('content_block_uidx_id_url')) {
            $this->addSql('CREATE UNIQUE INDEX content_block_uidx_id_url ON content_block (id, url)');
        }
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('content_block')) {
            return;
        }

        $table = $schema->getTable('content_block');

        if ($table->hasIndex('content_block_uidx_id_url')) {
            $this->addSql('DROP INDEX content_block_uidx_id_url ON content_block');
        }

        if ($table->hasColumn('pk')) {
            $this->addSql('ALTER TABLE content_block DROP PRIMARY KEY, DROP pk, ADD PRIMARY KEY (id)');
        }
    }
}
