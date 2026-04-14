# Architektur

## Überblick

Die Anwendung ist eine **Symfony-7.2-API** mit **API Platform 4**, **Doctrine ORM 3** und einem **Twig-basierten Admin-Panel**. Es wird **PHP 8.2+** und **MySQL 9** (lokal typischerweise per Docker Compose) verwendet. Die **REST-API** ist unter einem festen URL-Präfix (z. B. `/api`) erreichbar und ist **stateless**. Das **Admin-Panel** nutzt **Sessions** und Formular-Login.

Benutzerorientierte Texte in der Weboberfläche sind **deutsch**.

---

## Verzeichnisstruktur

```
src/
  ApiResource/     # API-Platform-Ressourcenklassen (derzeit leer; Entitäten sind direkt als API-Ressourcen annotiert)
  Command/         # Symfony-Konsolenbefehle
  Controller/      # HTTP-Controller; Admin unter Controller/Admin/
  Entity/          # Doctrine-Entitäten inkl. API-Platform- und Validierungs-Attribute
  Form/            # Symfony-Formular-Typen
  Repository/      # Doctrine-Repositories
config/            # Symfony-YAML-Konfiguration (Bundles, Routen, Security, API Platform, …)
migrations/        # Doctrine-Migrationen
templates/         # Twig-Templates; Admin-Basis: admin/layout.html.twig
public/            # Web-Root (Front-Controller)
```

**API Platform:** Ressourcen werden aktuell direkt auf **Entitätsklassen** mit `#[ApiResource]` und einzelnen Operationen (`Get`, `Post`, …) definiert — keine separaten API-Ressourcen-Klassen unter `ApiResource/`.

**Admin:** Controller unter `App\Controller\Admin`, übrige HTTP-Controller unter `App\Controller`.

**Templates:** Namensschema `templates/{modul}/{entität}/{aktion}.html.twig`; wiederverwendbare Partials mit Unterstrich-Präfix, z. B. `_partial.html.twig`.

---

## Schichten und Zuständigkeiten

| Bereich | Technik | Rolle |
|--------|---------|--------|
| HTTP / HTML-Admin | Symfony Controller, Twig, Forms | CRUD-Oberflächen, Login |
| HTTP / API | API Platform + Serializer + Validator | JSON(-LD) etc., OpenAPI/Docs |
| Domäne & Persistenz | Doctrine ORM | Entitäten, Repositories, Migrationen |
| Sicherheit | Symfony Security | `form_login`, Rollen, `access_control` |

Konfiguration liegt überwiegend in `config/packages/` (u. a. `api_platform.yaml`, `doctrine.yaml`, `security.yaml`, `framework.yaml`).

---

## Datenbank

- **Mapping:** ausschließlich **PHP-8-Attribute** (keine Annotations, kein XML).
- **Naming:** Underscore-Strategie — Eigenschaften in `camelCase` werden zu `snake_case`-Spalten.
- **Migrationen:** Änderungen am Schema über `symfony console make:migration` und `doctrine:migrations:migrate`.

---

## API Platform (Kurz)

- In `config/packages/api_platform.yaml` u. a. `route_prefix: /api`, statelesse Operationen, Cache-Header mit `Vary`.
- Ressourcen und Serialisierungsgruppen sitzen an den Entitäten (`{entity}:read` / `{entity}:write`).

Details zu einzelnen URLs: [`routing.md`](routing.md).

---

## Docker & lokaler Betrieb

```bash
docker compose up -d       # MySQL starten
docker compose down        # stoppen
docker compose down -v     # stoppen inkl. Volumes
```

Typische Entwicklungsschritte:

```bash
composer install
docker compose up -d
symfony console doctrine:migrations:migrate
symfony console app:create-admin
symfony server:start
# oder: php -S localhost:8000 -t public/
```

---

## Geplante Erweiterungen (ohne festes Tooling im Projekt)

- **Tests:** PHPUnit über `symfony/phpunit-bridge`, Namespace `App\Tests\` unter `tests/`.
- **Statische Analyse / Formatierung:** z. B. PHPStan, PHP-CS-Fixer (derzeit nicht installiert).
