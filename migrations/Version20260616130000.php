<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260616130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Restore separated media references for departments, contacts and locations";
    }

    public function up(Schema $schema): void
    {
        $departmentIcons = $this->loadJson("departments_icon_media_items.json");
        foreach ($departmentIcons as $row) {
            $this->addSql(
                "UPDATE department SET icon_id = ? WHERE id = ?",
                [$row["media_item_id"], $row["id"]],
            );
        }

        $contactPictures = $this->loadJson("contact_persons_picture_media_items.json");
        foreach ($contactPictures as $row) {
            $this->addSql(
                "UPDATE contact_person SET picture_id = ? WHERE id = ?",
                [$row["media_item_id"], $row["id"]],
            );
        }

        $locationPictures = $this->loadJson("locations_picture_media_items.json");
        foreach ($locationPictures as $row) {
            $this->addSql(
                "UPDATE location SET picture_id = ? WHERE id = ?",
                [$row["media_item_id"], $row["id"]],
            );
        }
    }

    public function down(Schema $schema): void
    {
        $departmentIcons = $this->loadJson("departments_icon_media_items.json");
        foreach ($departmentIcons as $row) {
            $this->addSql(
                "UPDATE department SET icon_id = NULL WHERE id = ?",
                [$row["id"]],
            );
        }

        $contactPictures = $this->loadJson("contact_persons_picture_media_items.json");
        foreach ($contactPictures as $row) {
            $this->addSql(
                "UPDATE contact_person SET picture_id = NULL WHERE id = ?",
                [$row["id"]],
            );
        }

        $locationPictures = $this->loadJson("locations_picture_media_items.json");
        foreach ($locationPictures as $row) {
            $this->addSql(
                "UPDATE location SET picture_id = NULL WHERE id = ?",
                [$row["id"]],
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadJson(string $filename): array
    {
        $path = dirname(__DIR__) . "/migration_data/" . $filename;
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException(
                sprintf("Cannot read migration data file: %s", $path),
            );
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
