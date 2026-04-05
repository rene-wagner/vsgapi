# VSG API

REST-API und Admin-Dashboard für den Sportverein **VSG Kugelberg e.V.** in Weißenfels.

## Technologie-Stack

| Bereich | Technologie |
|--------|-------------|
| Framework | [Symfony](https://symfony.com/) 7.2 (PHP 8.2+) |
| API | [API Platform](https://api-platform.com/) 4 |
| Admin-Oberfläche | [Twig](https://twig.symfony.com/)-Templates, Symfony Forms |
| Datenbank | [Doctrine ORM](https://www.doctrine-project.org/) 3, [MySQL](https://www.mysql.com/) 9 (lokal per Docker Compose) |

Die API ist unter dem konfigurierten Präfix (z. B. `/api`) erreichbar; das Admin-Panel ist eine stateful Web-Oberfläche mit Anmeldung.

## Schnellstart

```bash
composer install
php bin/console importmap:install   # Bootstrap, Font Awesome & Co. → assets/vendor/
docker compose up -d
php bin/console doctrine:migrations:migrate
php bin/console app:create-admin
symfony server:start
# alternativ: php -S localhost:8000 -t public/
```

Umgebungsvariablen in `.env` bzw. `.env.local` anpassen (u. a. Datenbank-URL). Details und Ladereihenfolge: [`docs/environment-variables.md`](docs/environment-variables.md).

## Dokumentation

| Thema | Datei |
|--------|--------|
| Architektur (Struktur, Schichten, Betrieb) | [`docs/architecture.md`](docs/architecture.md) |
| Konventionen (Code, Twig, Sicherheit, UX) | [`docs/conventions.md`](docs/conventions.md) |
| Routen (API, Admin, Login) | [`docs/routing.md`](docs/routing.md) |
| Umgebungsvariablen (`.env`, `.env.dev`, `.env.prod`) | [`docs/environment-variables.md`](docs/environment-variables.md) |
