<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seed initial data from migration_data/*.json.
 *
 * Import order respects FK dependencies:
 *   users → categories → media_folders → media_items → contact_persons → locations → departments
 *
 * media_folder.parent_id is a self-reference; root folders (parent_id=null) are inserted first,
 * then child folders are inserted with their parent_id set directly.
 *
 * down() deletes all imported rows but does NOT drop tables (that is the schema migration's job).
 */
final class Version20260414130000 extends AbstractMigration
{
    private const IMPORT_TIMESTAMP = '2026-04-14 12:00:00';

    public function getDescription(): string
    {
        return 'Seed initial data (users, categories, media, contacts, locations, departments)';
    }

    public function up(Schema $schema): void
    {
        // --- users ---
        $users = $this->loadJson('users.json');
        foreach ($users as $row) {
            $this->addSql(
                "INSERT INTO `user` (id, email, roles, password, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $row['id'],
                    $row['email'],
                    json_encode($row['roles']),
                    $row['password'],
                    $row['first_name'],
                    $row['last_name'],
                ],
            );
        }

        // --- categories ---
        $categories = $this->loadJson('categories.json');
        foreach ($categories as $row) {
            $this->addSql(
                "INSERT INTO category (id, name, slug) VALUES (?, ?, ?)",
                [$row['id'], $row['name'], $row['slug']],
            );
        }

        // --- media_folders (root folders first, then children) ---
        $folders = $this->loadJson('media_folders.json');
        $roots = array_filter($folders, fn (array $f): bool => $f['parent_id'] === null);
        $children = array_filter($folders, fn (array $f): bool => $f['parent_id'] !== null);

        foreach ($roots as $row) {
            $this->addSql(
                "INSERT INTO media_folder (id, name, created_at, updated_at, parent_id) VALUES (?, ?, ?, ?, NULL)",
                [$row['id'], $row['name'], self::IMPORT_TIMESTAMP, self::IMPORT_TIMESTAMP],
            );
        }
        foreach ($children as $row) {
            $this->addSql(
                "INSERT INTO media_folder (id, name, created_at, updated_at, parent_id) VALUES (?, ?, ?, ?, ?)",
                [$row['id'], $row['name'], self::IMPORT_TIMESTAMP, self::IMPORT_TIMESTAMP, $row['parent_id']],
            );
        }

        // --- media_items ---
        $items = $this->loadJson('media_items.json');
        foreach ($items as $row) {
            $this->addSql(
                "INSERT INTO media_item (id, name, original_filename, mime_type, extension, type, size_bytes, path, thumbnail_path, description, crop_x, crop_y, crop_width, crop_height, created_at, updated_at, folder_id, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $row['id'],
                    $row['name'],
                    $row['original_filename'],
                    $row['mime_type'],
                    $row['extension'],
                    $row['type'],
                    $row['size_bytes'],
                    $row['path'],
                    $row['thumbnail_path'],
                    $row['description'],
                    $row['crop_x'],
                    $row['crop_y'],
                    $row['crop_width'],
                    $row['crop_height'],
                    $row['created_at'],
                    $row['updated_at'],
                    $row['folder_id'],
                    $row['category_id'],
                ],
            );
        }

        // --- contact_persons ---
        $contacts = $this->loadJson('contact_persons.json');
        foreach ($contacts as $row) {
            $this->addSql(
                "INSERT INTO contact_person (id, slug, first_name, last_name, position, email, phone, address, is_board, picture_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $row['id'],
                    $row['slug'],
                    $row['first_name'],
                    $row['last_name'],
                    $row['position'],
                    $row['email'],
                    $row['phone'],
                    $row['address'],
                    $row['is_board'] ?? false,
                    $row['picture_id'],
                ],
            );
        }

        // --- locations ---
        $locations = $this->loadJson('locations.json');
        foreach ($locations as $row) {
            $this->addSql(
                "INSERT INTO location (id, name, street, city, maps_url, picture_id) VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $row['id'],
                    $row['name'],
                    $row['street'],
                    $row['city'],
                    $row['maps_url'],
                    $row['picture_id'],
                ],
            );
        }

        // --- departments ---
        $departments = $this->loadJson('departments.json');
        foreach ($departments as $row) {
            $this->addSql(
                "INSERT INTO department (id, name, slug, description, icon_id) VALUES (?, ?, ?, ?, ?)",
                [
                    $row['id'],
                    $row['name'],
                    $row['slug'],
                    $row['description'],
                    $row['icon_id'],
                ],
            );
        }

        // --- department_statistics ---
        $statistics = $this->loadJson('department_statistics.json');
        foreach ($statistics as $row) {
            $this->addSql(
                "INSERT INTO department_statistic (id, label, value, department_id) VALUES (?, ?, ?, ?)",
                [
                    $row['id'],
                    $row['label'],
                    $row['value'],
                    $row['department_id'],
                ],
            );
        }

        // --- department_training_groups ---
        $trainingGroups = $this->loadJson('department_training_groups.json');
        foreach ($trainingGroups as $row) {
            $this->addSql(
                "INSERT INTO department_training_group (id, name, age_range, department_id) VALUES (?, ?, ?, ?)",
                [
                    $row['id'],
                    $row['name'],
                    $row['age_range'],
                    $row['department_id'],
                ],
            );
        }

        // --- department_training_sessions ---
        $trainingSessions = $this->loadJson('department_training_sessions.json');
        foreach ($trainingSessions as $row) {
            $this->addSql(
                "INSERT INTO department_training_session (id, day, time, department_training_group_id, location_id) VALUES (?, ?, ?, ?, ?)",
                [
                    $row['id'],
                    $row['day'],
                    $row['time'],
                    $row['department_training_group_id'],
                    $row['location_id'],
                ],
            );
        }

        // --- posts ---
        $posts = $this->loadJson('posts.json');
        foreach ($posts as $row) {
            $this->addSql(
                "INSERT INTO post (id, title, slug, content, published, hits, old_post, created_at, updated_at, author_id, picture_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)",
                [
                    $row['id'],
                    $row['title'],
                    $row['slug'],
                    $row['content'],
                    $row['published'],
                    $row['hits'],
                    $row['old_post'],
                    $row['created'],
                    $row['modified'],
                    $row['author_id'],
                ],
            );
        }

        // --- post_categories ---
        foreach ($posts as $row) {
            $this->addSql(
                'INSERT INTO post_category (post_id, category_id) VALUES (?, ?)',
                [$row['id'], $row['catid']],
            );
        }

        // --- Reset auto-increment values to max(id) + 1 per table ---
        // MySQL does not allow subqueries in ALTER TABLE AUTO_INCREMENT,
        // so we compute the next value from the imported data.
        $autoIncrement = [
            '`user`' => max(array_column($users, 'id')) + 1,
            'category' => max(array_column($categories, 'id')) + 1,
            'media_folder' => max(array_column($folders, 'id')) + 1,
            'media_item' => max(array_column($items, 'id')) + 1,
            'contact_person' => max(array_column($contacts, 'id')) + 1,
            'location' => max(array_column($locations, 'id')) + 1,
            'department' => max(array_column($departments, 'id')) + 1,
            'department_statistic' => max(array_column($statistics, 'id')) + 1,
            'department_training_group' => max(array_column($trainingGroups, 'id')) + 1,
            'department_training_session' => max(array_column($trainingSessions, 'id')) + 1,
            'post' => max(array_column($posts, 'id')) + 1,
        ];
        foreach ($autoIncrement as $table => $nextId) {
            $this->addSql(sprintf('ALTER TABLE %s AUTO_INCREMENT = %d', $table, $nextId));
        }
    }

    public function down(Schema $schema): void
    {
        // Delete imported data in reverse FK dependency order.
        // Does NOT drop tables — that is the schema migration's responsibility.
        $this->addSql('DELETE FROM department_training_session');
        $this->addSql('DELETE FROM department_training_group');
        $this->addSql('DELETE FROM department_statistic');
        $this->addSql('DELETE FROM department');
        $this->addSql('DELETE FROM location');
        $this->addSql('DELETE FROM contact_person');
        $this->addSql('DELETE FROM post_category');
        $this->addSql('DELETE FROM post');
        $this->addSql('DELETE FROM media_item');
        $this->addSql('DELETE FROM media_folder');
        $this->addSql('DELETE FROM category');
        $this->addSql('DELETE FROM `user`');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadJson(string $filename): array
    {
        $path = dirname(__DIR__) . '/migration_data/' . $filename;
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException(sprintf('Cannot read migration data file: %s', $path));
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
