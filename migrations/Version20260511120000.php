<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Benennt PDF-Felder fuer Aufnahmeantraege um und ergaenzt Felder fuer Aufsichtspflicht-PDFs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE membership_application CHANGE pdf_filename membership_application_pdf_filename VARCHAR(255) NOT NULL, CHANGE pdf_path membership_application_pdf_path VARCHAR(512) NOT NULL, ADD supervision_duty_pdf_filename VARCHAR(255) DEFAULT NULL, ADD supervision_duty_pdf_path VARCHAR(512) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE membership_application DROP supervision_duty_pdf_filename, DROP supervision_duty_pdf_path, CHANGE membership_application_pdf_filename pdf_filename VARCHAR(255) NOT NULL, CHANGE membership_application_pdf_path pdf_path VARCHAR(512) NOT NULL');
    }
}
