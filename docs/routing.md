# Routing

Diese Übersicht listet die in Symfony registrierten Routen. API Platform ergängt automatisch weitere Endpunkte, sobald neue Ressourcen (`#[ApiResource]`) hinzukommen. Aktuellen Stand liefert:

```bash
symfony console debug:router
```

## Konfiguration

- Controller-Routen: PHP-Attribute unter `src/Controller/` (siehe `config/routes.yaml`).
- API Platform: `config/packages/api_platform.yaml` → `route_prefix: /api` für API-Ressourcen.

## Zugriffskontrolle (Kurzüberblick)

Laut `config/packages/security.yaml`:

- `/login` — öffentlich (`PUBLIC_ACCESS`)
- `/media/` — öffentlich (`PUBLIC_ACCESS`), damit ausgelieferte Mediendateien (Bilder, PDFs, Thumbnails) per direkter URL ohne Session erreichbar sind
- `/admin` — `ROLE_ADMIN`
- Weitere Pfade (z. B. `/api`) sind dort nicht einzeln eingetragen; bei Bedarf `access_control` erweitern. Die Medien-JSON-API unter `/api/media_*` erfordert wie andere geschützte API-Ressourcen eine authentifizierte Session (`IS_AUTHENTICATED_FULLY` auf den betreffenden `#[ApiResource]`-Operationen).

---

## API Platform — Dokumentation & Metadaten

| Name | Methoden | Pfad | Hinweis |
|------|----------|------|---------|
| `api_doc` | GET, HEAD | `/docs.{_format}` | API-Dokumentation (Format z. B. `html`, `json`) |
| `api_entrypoint` | GET, HEAD | `/{index}.{_format}` | Einstiegspunkt (z. B. Index der API) |
| `api_jsonld_context` | GET, HEAD | `/contexts/{shortName}.{_format}` | JSON-LD-Kontexte |
| `api_genid` | GET, HEAD | `/.well-known/genid/{id}` | Well-known / GenID |
| `api_validation_errors` | GET, HEAD | `/validation_errors/{id}` | Validierungsfehler (Hydra-kompatibel) |
| `_api_errors` | GET | `/errors/{status}.{_format}` | Fehlerdarstellung nach Status |
| `_api_validation_errors_problem` | GET | `/validation_errors/{id}` | Problem Details |
| `_api_validation_errors_hydra` | GET | `/validation_errors/{id}` | Hydra |
| `_api_validation_errors_jsonapi` | GET | `/validation_errors/{id}` | JSON:API |
| `_api_validation_errors_xml` | GET | `/validation_errors/{id}` | XML |

`{_format}` steht für das angeforderte Inhaltsformat (z. B. `jsonld`, `json`).

---

## API Platform — Ressource `User` (Präfix `/api`)

Konfiguriert in `src/Entity/User.php` (`GetCollection`, `Get`, `Post`, `Patch`, `Delete`).

| Name | Methoden | Pfad |
|------|----------|------|
| `_api_/api/users{._format}_get_collection` | GET | `/api/users.{_format}` |
| `_api_/api/users/{id}{._format}_get` | GET | `/api/users/{id}.{_format}` |
| `_api_/api/users{._format}_post` | POST | `/api/users.{_format}` |
| `_api_/api/users/{id}{._format}_patch` | PATCH | `/api/users/{id}.{_format}` |
| `_api_/api/users/{id}{._format}_delete` | DELETE | `/api/users/{id}.{_format}` |

`{id}` ist die numerische Entitäts-ID.

---

## API Platform — Ressource `Category` (Präfix `/api`)

Konfiguriert in `src/Entity/Category.php` (`GetCollection`, `Get`). `slug` ist als API-Identifier gesetzt.

| Name | Methoden | Pfad |
|------|----------|------|
| `_api_/api/categories{._format}_get_collection` | GET | `/api/categories.{_format}` |
| `_api_/api/categories/{slug}{._format}_get` | GET | `/api/categories/{slug}.{_format}` |

`{slug}` ist der SEO-konforme Kategorien-Slug (z. B. `wohnzimmer-lampen`).

---

## API Platform — Ressource `Post` (Präfix `/api`)

Konfiguriert in `src/Entity/Post.php` (`GetCollection`, `Get`). `slug` ist als API-Identifier gesetzt.

| Name | Methoden | Pfad |
|------|----------|------|
| `_api_/api/posts{._format}_get_collection` | GET | `/api/posts.{_format}` |
| `_api_/api/posts/{slug}{._format}_get` | GET | `/api/posts/{slug}.{_format}` |

`{slug}` ist der eindeutige Beitrags-Slug (z. B. `mein-beitrag`).

---

## API Platform — Ressource `Location` (Präfix `/api`)

Konfiguriert in `src/Entity/Location.php` (`GetCollection`, `Get`).

| Name | Methoden | Pfad |
|------|----------|------|
| `_api_/api/locations{._format}_get_collection` | GET | `/api/locations.{_format}` |
| `_api_/api/locations/{id}{._format}_get` | GET | `/api/locations/{id}.{_format}` |

`{id}` ist die numerische Entitäts-ID. JSON-Felder u. a. `name`, `street`, `city`, optional `mapsUrl`.

---

## API Platform — Ressource `Department` (Präfix `/api`)

Konfiguriert in `src/Entity/Department.php` (`GetCollection`, `Get`). `slug` ist als API-Identifier gesetzt. Unterobjekte (`departmentStats`, `trainingGroups` mit `departmentTrainingSessions` und eingebetteten `locations`) sind nur über diese Ressource serialisiert, ohne eigene API-Endpunkte.

| Name | Methoden | Pfad |
|------|----------|------|
| `_api_/api/departments{._format}_get_collection` | GET | `/api/departments.{_format}` |
| `_api_/api/departments/{slug}{._format}_get` | GET | `/api/departments/{slug}.{_format}` |

`{slug}` ist der eindeutige Abteilungs-Slug (z. B. `handball`). Die Antwort enthält verschachtelt Statistiken, Trainingsgruppen, Trainingseinheiten und referenzierte Sportstätten (`Location`).

---

## API Platform — Ressource `ContactPerson` (Präfix `/api`)

Konfiguriert in `src/Entity/ContactPerson.php` (`GetCollection`, `Get`). `slug` ist als API-Identifier gesetzt.

| Name | Methoden | Pfad |
|------|----------|------|
| `_api_/api/contact_people{._format}_get_collection` | GET | `/api/contact_people.{_format}` |
| `_api_/api/contact_people/{slug}{._format}_get` | GET | `/api/contact_people/{slug}.{_format}` |

`{slug}` ist der eindeutige Slug der Kontaktperson (z. B. `max-mustermann`). JSON-Felder u. a. `id`, `slug`, `firstName`, `lastName`, optional `email`, `phone`, `address`.

---

## API Platform — Ressource `MediaFolder` (Präfix `/api`)

Konfiguriert in `src/Entity/MediaFolder.php` (`GetCollection`, `Get`). Zugriff nur für authentifizierte Benutzer.

| Name | Methoden | Pfad |
|------|----------|------|
| `_api_/api/media_folders{._format}_get_collection` | GET | `/api/media_folders.{_format}` |
| `_api_/api/media_folders/{id}{._format}_get` | GET | `/api/media_folders/{id}.{_format}` |

`{id}` ist die numerische Ordner-ID.

---

## API Platform — Ressource `MediaItem` (Präfix `/api`)

Konfiguriert in `src/Entity/MediaItem.php`. Zugriff nur für authentifizierte Benutzer. Die Antwort enthält u. a. berechnete Felder `url`, `thumbnail_url`, `size_human`, `folder_id`, `category_id` (siehe `MediaItemNormalizer`).

| Name | Methoden | Pfad | Hinweis |
|------|----------|------|---------|
| `_api_/api/media_items_get_collection` | GET | `/api/media_items` | Paginierte Liste (20 pro Seite). Filter: `?category=/api/categories/{slug}` (nach Kategorie), `?folder=<id>` (nach Ordner). `page`-Parameter für Pagination. |
| `_api_/api/media_items/{id}_get` | GET | `/api/media_items/{id}` | Einzelnes Medium |
| `media_item_upload` | POST | `/api/media_items/upload` | Multipart: `file` (Pflicht), optional `folder`, `category`, `description`, `name` |
| `media_item_copy` | POST | `/api/media_items/{id}/copy` | Optionaler JSON-Body: `{"folder": <numerische Ordner-ID>}` |
| `_api_/api/media_items/{id}_patch` | PATCH | `/api/media_items/{id}` | Metadaten / Ordner (Verschieben) |
| `_api_/api/media_items/{id}_delete` | DELETE | `/api/media_items/{id}` | Löscht Datensatz sowie Dateien auf dem Server |

`{id}` ist die numerische Medien-ID.

---

## Öffentliche Mediendateien (kein API Platform)

Auslieferung der gespeicherten Dateien aus `var/media` (relativ zu `media.storage_dir`). Die in der Datenbank gespeicherten Pfade (`path`, `thumbnail_path`) werden unter dieser URL-Präfixkette erreichbar; vollständige Links setzen sich mit `MEDIA_HOST` und `media.public_path_prefix` (Standard `/media/files`) zusammen (`MediaUrlService`).

| Name | Methoden | Pfad | Hinweis |
|------|----------|------|---------|
| `media_file_serve` | GET | `/media/files/{path}` | `{path}` ist der relative Speicherpfad (kann Schrägstriche enthalten). Öffentlich (`PUBLIC_ACCESS`). |

Controller: `App\Controller\MediaFileServeController`.

---

## Admin-Bereich

| Name | Methoden | Pfad | Zweck |
|------|----------|------|--------|
| `admin_dashboard` | alle | `/admin` | Admin-Startseite |
| `admin_user_index` | GET | `/admin/users` | Benutzerliste |
| `admin_user_new` | GET, POST | `/admin/users/new` | Neuer Benutzer |
| `admin_user_show` | GET | `/admin/users/{id}` | Benutzer anzeigen |
| `admin_user_edit` | GET, POST | `/admin/users/{id}/edit` | Benutzer bearbeiten |
| `admin_user_delete` | POST | `/admin/users/{id}` | Benutzer löschen (mit CSRF) |
| `admin_category_index` | GET | `/admin/categories` | Kategorienliste |
| `admin_category_new` | GET, POST | `/admin/categories/new` | Neue Kategorie |
| `admin_category_show` | GET | `/admin/categories/{id}` | Kategorie anzeigen |
| `admin_category_edit` | GET, POST | `/admin/categories/{id}/edit` | Kategorie bearbeiten |
| `admin_category_delete` | POST | `/admin/categories/{id}` | Kategorie löschen (mit CSRF) |
| `admin_post_index` | GET | `/admin/posts` | Beitragsliste |
| `admin_post_new` | GET, POST | `/admin/posts/new` | Neuer Beitrag |
| `admin_post_show` | GET | `/admin/posts/{id}` | Beitrag anzeigen |
| `admin_post_edit` | GET, POST | `/admin/posts/{id}/edit` | Beitrag bearbeiten |
| `admin_post_delete` | POST | `/admin/posts/{id}` | Beitrag löschen (mit CSRF) |
| `admin_location_index` | GET | `/admin/locations` | Sportstättenliste |
| `admin_location_new` | GET, POST | `/admin/locations/new` | Neue Sportstätte |
| `admin_location_show` | GET | `/admin/locations/{id}` | Sportstätte anzeigen |
| `admin_location_edit` | GET, POST | `/admin/locations/{id}/edit` | Sportstätte bearbeiten |
| `admin_location_delete` | POST | `/admin/locations/{id}` | Sportstätte löschen (mit CSRF) |
| `admin_department_index` | GET | `/admin/departments` | Sportabteilungen-Liste |
| `admin_department_new` | GET, POST | `/admin/departments/new` | Neue Abteilung |
| `admin_department_show` | GET | `/admin/departments/{id}` | Abteilung anzeigen |
| `admin_department_edit` | GET, POST | `/admin/departments/{id}/edit` | Abteilung bearbeiten |
| `admin_department_delete` | POST | `/admin/departments/{id}` | Abteilung löschen (mit CSRF) |
| `admin_contact_person_index` | GET | `/admin/contact-people` | Kontaktpersonen-Liste |
| `admin_contact_person_new` | GET, POST | `/admin/contact-people/new` | Neue Kontaktperson |
| `admin_contact_person_show` | GET | `/admin/contact-people/{id}` | Kontaktperson anzeigen |
| `admin_contact_person_edit` | GET, POST | `/admin/contact-people/{id}/edit` | Kontaktperson bearbeiten |
| `admin_contact_person_delete` | POST | `/admin/contact-people/{id}` | Kontaktperson löschen (mit CSRF) |
| `admin_mediathek_index` | GET, POST | `/admin/mediathek` | Mediathek: Liste, Upload, Ordnerfilter (`?folder=<id>`), Kopieren/Löschen |
| `admin_mediathek_item_edit` | GET, POST | `/admin/mediathek/items/{id}/edit` | Medium bearbeiten (Name, Beschreibung, Kategorie, Ordner/Verschieben) |
| `admin_mediathek_item_copy` | POST | `/admin/mediathek/items/{id}/copy` | Medium duplizieren (optional `folder_id` im Formular) |
| `admin_mediathek_item_delete` | POST | `/admin/mediathek/items/{id}/delete` | Medium inkl. Dateien löschen (mit CSRF) |
| `admin_media_folder_index` | GET | `/admin/media-folders` | Medien-Ordnerliste |
| `admin_media_folder_new` | GET, POST | `/admin/media-folders/new` | Neuer Medien-Ordner |
| `admin_media_folder_edit` | GET, POST | `/admin/media-folders/{id}/edit` | Medien-Ordner bearbeiten |
| `admin_media_folder_delete` | POST | `/admin/media-folders/{id}` | Medien-Ordner löschen (mit CSRF; nur wenn leer) |

Controller: `App\Controller\Admin\DashboardController`, `App\Controller\Admin\UserController`, `App\Controller\Admin\CategoryController`, `App\Controller\Admin\PostController`, `App\Controller\Admin\LocationController`, `App\Controller\Admin\DepartmentController`, `App\Controller\Admin\ContactPersonController`, `App\Controller\Admin\MediaLibraryController`, `App\Controller\Admin\MediaFolderController`.

---

## Sicherheit / Anmeldung

| Name | Methoden | Pfad | Zweck |
|------|----------|------|--------|
| `app_login` | alle | `/login` | Formular-Login (`form_login`) |
| `app_logout` | alle | `/logout` | Abmeldung |

Controller: `App\Controller\SecurityController`.

---

## Sonstiges (Framework)

| Name | Methoden | Pfad | Hinweis |
|------|----------|------|---------|
| `_preview_error` | alle | `/_error/{code}.{_format}` | Symfony-Fehler-Vorschau (Entwicklung) |

In der Entwicklungsumgebung können zusätzlich Profiler- und Web-Debug-Toolbar-Routen unter `/_wdt` und `/_profiler` aktiv sein.
