# AGENTS.md — VSG API

Symfony 7.2 + API Platform 4 + Doctrine ORM 3 + Twig admin panel. PHP 8.2+, MySQL 9 via Docker Compose. German-language UI. AssetMapper (no Node.js/Webpack).

## Setup After Clone

```bash
composer install
php bin/console importmap:install   # downloads assets/vendor/ from importmap.php (gitignored)
docker compose up -d
php bin/console doctrine:migrations:migrate
php bin/console app:create-admin
symfony server:start                 # or: php -S localhost:8000 -t public/
```

`importmap:install` saying "nothing to install" is normal when `assets/vendor/` already matches `importmap.php` — not an error.

## Key Commands

```bash
php bin/console make:migration                # after entity changes
php bin/console doctrine:migrations:migrate    # apply migrations
php bin/console doctrine:schema:validate      # check mapping
php bin/console cache:clear
php bin/console asset-map:compile              # production/CI: compile versioned assets
php bin/console importmap:require <package>   # add a JS/CSS package (updates importmap.php)
php bin/console app:create-admin               # create admin user interactively
```

No test framework or linter is configured yet. PHPUnit via `symfony/phpunit-bridge` when adding tests.

## Architecture

```
src/
  Api/             # empty — not used
  ApiResource/     # empty — API resources live on Entity classes via #[ApiResource]
  Command/         # Symfony console commands
  Controller/
    Admin/         # admin panel controllers (ROLE_ADMIN required)
    Api/           # custom API Platform controllers (MediaItemUpload, MediaItemCopy)
  Entity/          # Doctrine entities with #[ApiResource], #[ORM\*], #[Assert\*] attributes
  Enum/            # PHP enums (e.g. MediaItemType)
  Form/            # Symfony form types + DataTransformer/
  Repository/      # ServiceEntityRepository<Entity>
  Serializer/      # MediaItemNormalizer (tagged priority 64)
  Service/Media/   # upload, copy, move, delete, URL generation, SVG sanitization
  State/           # API Platform state processors (MediaItemDeleteProcessor)
templates/
  admin/           # extends admin/layout.html.twig → base.html.twig
  form/            # shared form partials
  security/        # login
config/packages/  # Symfony YAML config
importmap.php      # AssetMapper import map (project root)
migrations/        # Doctrine migrations
```

API Platform resources are defined via `#[ApiResource]` directly on Entity classes — do **not** create files in `src/ApiResource/`. Serialization groups follow the `{entity}:read` / `{entity}:write` pattern.

Custom API Platform endpoints (upload, copy) use the `controller:` option with `deserialize: false`, see `MediaItem` entity for the pattern.

## Conventions That Differ From Defaults

- **No `declare(strict_types=1)`** in application code. Migrations do use it — follow existing files.
- **Setter methods return `static`**, not `self`: `public function setEmail(string $email): static`
- **Controller actions use method injection** (autowired parameters), not constructor injection.
- **Forms rendered manually** with `form_label`, `form_widget`, `form_errors` — never `form_row`.
- **No `form_row`** anywhere in templates.
- **All user-facing strings in German**: flash messages, labels, validation errors, template text.
- **One `use` statement per class** — no grouped imports (`use A, B`).
- **Import order**: PHP built-in → third-party (ApiPlatform, Doctrine, Symfony) → App namespace.
- **Trailing comma** on multi-line parameter lists.
- Properties are `private` with getter/setter pairs; IDs: `private ?int $id = null`.

## AssetMapper & Frontend

- **AssetMapper** handles all CSS/JS — no Node.js, no Webpack Encore.
- **Bootstrap 5** CSS imported in `assets/app.js` from the import map.
- **Font Awesome Free** (not Pro): must import **both** `fontawesome.min.css` and `solid.min.css` in `assets/app.js`. Without `solid.min.css`, icons appear as blank squares — this is an easy mistake.
- Additional app styles in `assets/styles/app.css`.
- **EasyMDE** (Markdown editor) and **Cropper.js** (image cropping) are in the importmap for admin use.
- After adding packages: `php bin/console importmap:require <package>`, then `importmap:install` to download.
- Production build: `php bin/console asset-map:compile`.
- Admin templates use `importmap('app')` — see `admin/layout.html.twig`.

## Media Subsystem

Configured in `config/services.yaml` with parameters:

| Parameter | Value | Purpose |
|-----------|-------|---------|
| `media.storage_dir` | `%kernel.project_dir%/var/media` | File storage on disk |
| `media.public_path_prefix` | `/media/files` | URL path prefix |
| `media.max_upload_bytes` | 10485760 (10 MB) | Upload size limit |
| `media.thumbnail_max_edge` | 320px | Thumbnail max dimension |

`MEDIA_HOST` env var (default `http://localhost:8000`) is injected into `MediaUrlService` for absolute URL generation.

File serving: `App\Controller\MediaFileServeController` serves from `var/media` at `/media/files/{path}` — these routes are **PUBLIC_ACCESS** (no auth required).

## Security

- `/login` — public (form_login with CSRF)
- `/media/` — public (serves stored files without auth)
- `/admin` — requires `ROLE_ADMIN`
- API under `/api` — stateless; individual `#[ApiResource]` operations enforce `IS_AUTHENTICATED_FULLY` where needed
- Admin panel is stateful (sessions)

## Doctrine

- Mapping type: **PHP 8 attributes** only (no XML, no annotations).
- Naming strategy: **underscore** — `camelCase` properties → `snake_case` columns automatically.
- Reserved table names use backticks: `#[ORM\Table(name: '`user`')]`.
- Create migration after entity changes: `php bin/console make:migration`.

## Environment

| Variable | Source | Notes |
|----------|--------|-------|
| `APP_ENV` | `.env` → `dev` | Determines which `.env.*` files load |
| `APP_SECRET` | `.env` | Replace in production |
| `DATABASE_URL` | `.env` | MySQL 9; Docker Compose: `mysql://user:secret@localhost:3306/database` |
| `DEFAULT_URI` | `.env` | URL generation in CLI contexts |
| `MEDIA_HOST` | `.env` | Base URL for absolute media links (no trailing slash) |

Load order: `.env` → `.env.local` → `.env.<APP_ENV>` → `.env.<APP_ENV>.local`. Real env vars win over files.