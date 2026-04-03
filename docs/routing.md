# Routing

Diese Übersicht listet die in Symfony registrierten Routen. API Platform ergängt automatisch weitere Endpunkte, sobald neue Ressourcen (`#[ApiResource]`) hinzukommen. Aktuellen Stand liefert:

```bash
php bin/console debug:router
```

## Konfiguration

- Controller-Routen: PHP-Attribute unter `src/Controller/` (siehe `config/routes.yaml`).
- API Platform: `config/packages/api_platform.yaml` → `route_prefix: /api` für API-Ressourcen.

## Zugriffskontrolle (Kurzüberblick)

Laut `config/packages/security.yaml`:

- `/login` — öffentlich (`PUBLIC_ACCESS`)
- `/admin` — `ROLE_ADMIN`
- Weitere Pfade (z. B. `/api`) sind dort nicht einzeln eingetragen; bei Bedarf `access_control` erweitern.

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

Controller: `App\Controller\Admin\DashboardController`, `App\Controller\Admin\UserController`, `App\Controller\Admin\CategoryController`, `App\Controller\Admin\PostController`, `App\Controller\Admin\LocationController`, `App\Controller\Admin\DepartmentController`, `App\Controller\Admin\ContactPersonController`.

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
