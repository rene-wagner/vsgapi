# AGENTS.md — VSG API

## Project Overview

Symfony 7.2 API project with API Platform 4, Doctrine ORM 3, and a Twig-based admin panel.
PHP 8.2+ required. MySQL 9 database via Docker Compose. German-language UI.
Frontend assets are built with **Webpack Encore**; the admin UI uses **Bootstrap 5** as its CSS framework, **Font Awesome Free** (solid icons) for pictograms, and **Vue.js 3** (via Symfony UX) for interactive components.

## Architecture

```
src/
  ApiResource/     # API Platform resource classes (empty, entities used directly for now)
  Command/         # Symfony console commands
  Controller/      # HTTP controllers (Admin/ subdirectory for admin panel)
  Entity/          # Doctrine ORM entities with API Platform attributes
  Form/            # Symfony form types
  Repository/      # Doctrine repositories
assets/
  app.js           # Webpack Encore entry point (imports CSS, Bootstrap, Vue, lazy modules)
  styles/app.css   # Custom CSS
  stimulus_bootstrap.js  # Stimulus app bootstrap
  controllers/     # Stimulus controllers (empty, for future use)
  vue/
    controllers/   # Vue.js controller components (Symfony UX)
    components/    # Vue.js reusable components
  easymde-init.js  # EasyMDE Markdown editor init (lazy)
  media-item-crop.js  # Cropper.js init (lazy)
  media-selector.js   # Media selector modal (lazy)
  mediathek-dropzone.js # File dropzone (lazy)
  marked-default-bridge.js # marked.js bridge for EasyMDE
config/            # Symfony YAML configuration
migrations/        # Doctrine migrations
templates/         # Twig templates (admin/ with layout.html.twig base)
webpack.config.js  # Webpack Encore configuration
```

API Platform resources are defined via PHP attributes directly on Entity classes.
Admin panel controllers live under `App\Controller\Admin`, standard controllers in `App\Controller`.
Templates follow `templates/{module}/{entity}/{action}.html.twig` — partials prefixed with `_`.

## Build & Run Commands

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm ci

# Start database
docker compose up -d

# Run migrations
php bin/console doctrine:migrations:migrate

# Clear cache
php bin/console cache:clear

# Frontend assets (Webpack Encore)
npm run dev          # development build (no watch)
npm run watch        # development build with file watcher
npm run dev-server   # development with hot module replacement
npm run build        # production build (minified, versioned)

# Create admin user
php bin/console app:create-admin

# Start dev server
symfony server:start    # or: php -S localhost:8000 -t public/
```

## Database

```bash
# Create a new migration after entity changes
php bin/console make:migration

# Run pending migrations
php bin/console doctrine:migrations:migrate

# Validate schema
php bin/console doctrine:schema:validate
```

Doctrine mapping uses **PHP 8 attributes** (not annotations or XML).
Naming strategy: `underscore` (camelCase properties → snake_case columns).

## Testing

No test framework is currently configured. When adding tests:

- Use PHPUnit via `symfony/phpunit-bridge`
- Test namespace: `App\Tests\` → `tests/`
- Run: `php bin/phpunit` or `./vendor/bin/phpunit`
- Single test: `php bin/phpunit tests/Path/To/TestFile.php`
- Single method: `php bin/phpunit --filter testMethodName`

## Linting & Static Analysis

No linters or static analysis tools are currently installed. When adding:

- PHPStan: `composer require --dev phpstan/phpstan` → `vendor/bin/phpstan analyse src/`
- PHP-CS-Fixer: `composer require --dev friendsofphp/php-cs-fixer` → `vendor/bin/php-cs-fixer fix`

## Code Style

### PHP General

- **No `declare(strict_types=1)`** in application code (migrations do use it)
- Use PHP 8.2+ features: named arguments, readonly properties, enums, fibers where appropriate
- One class per file, PSR-4 autoloading under `App\` namespace

### Imports

- Group order: PHP built-in → third-party (ApiPlatform, Doctrine, Symfony) → App namespace
- One `use` statement per class — no grouped imports
- Aliased imports for constraints: `Doctrine\ORM\Mapping as ORM`, `Symfony\Component\Validator\Constraints as Assert`

### Type Hints

- **All** method parameters and return types must be type-hinted
- Nullable properties use `?Type` syntax: `private ?string $email = null`
- Setters return `static` for fluent API: `public function setEmail(string $email): static`
- Getters return nullable when property is nullable: `public function getEmail(): ?string`
- Use `@var` and `@return` docblocks only for generic types: `/** @var list<string> */`
- `void` return type for methods with no return value
- `never` return type where appropriate (e.g., logout stubs)

### Naming

- Classes: `PascalCase` — `UserController`, `CreateAdminCommand`
- Methods/properties: `camelCase` — `getFullName()`, `$firstName`
- Routes: `snake_case` with module prefix — `admin_user_index`, `admin_user_edit`
- Route paths: lowercase kebab or simple — `/admin/users`, `/admin/users/{id}/edit`
- Console commands: `kebab-case` with app prefix — `app:create-admin`
- Form options: `snake_case` — `is_edit`, `data_class`
- Twig templates: `snake_case.html.twig`, partials prefixed with `_`

### Classes & Methods

- Controllers extend `AbstractController`
- Repositories extend `ServiceEntityRepository<Entity>`
- Forms extend `AbstractType`
- Commands extend `Command` with `#[AsCommand]` attribute
- Constructor promotion with `private readonly` for service injection in commands/services
- Controller actions use **method injection** (autowired parameters), not constructor injection
- Trailing comma on multi-line parameter lists

### Doctrine Entities

- PHP 8 attributes for mapping: `#[ORM\Entity]`, `#[ORM\Column]`, `#[ORM\Id]`
- Validation via Symfony attributes: `#[Assert\NotBlank]`, `#[Assert\Email]`
- API Platform via attributes: `#[ApiResource(...)]` with explicit operations list
- Serialization groups: `{entity}:read`, `{entity}:write` pattern
- Properties are `private` with getter/setter pairs
- ID fields: `private ?int $id = null` with auto-generation
- Table names quoted when matching reserved words: `#[ORM\Table(name: '`user`')]`

### Error Handling

- Validation errors returned via Symfony Validator (`$this->validator->validate()`)
- CSRF protection on destructive actions: `$this->isCsrfTokenValid(...)`
- Flash messages for user feedback: `$this->addFlash('success', '...')`
- UX messages are in **German**: "Benutzer wurde erfolgreich erstellt."

### Twig Templates

- Admin templates extend `admin/layout.html.twig` (which extends `base.html.twig`)
- Blocks: `{% block title %}`, `{% block content %}`, `{% block stylesheets %}`, `{% block javascripts %}`
- Load CSS/JS through **Webpack Encore**: use `encore_entry_link_tags('app')` and `encore_entry_script_tags('app')` in `base.html.twig`; **Bootstrap 5** supplies layout, components, and utilities
- **Icons**: use **Font Awesome** classes (e.g. `fa-solid fa-trash`) in markup; CSS is pulled in via `assets/app.js`. Prefer `aria-label` on icon-only controls and `aria-hidden="true"` on decorative `<i>` elements
- Forms rendered manually with `form_label`, `form_widget`, `form_errors` (not `form_row`)
- Use `path()` for route generation, never hardcode URLs
- HTML lang is `de`

### Security

- Authentication: form_login with CSRF
- Admin routes (`/admin/*`) require `ROLE_ADMIN`
- API routes served under `/api` prefix (configured in api_platform.yaml)
- API is stateless; admin panel is stateful with sessions

## Configuration

- Services: autowire + autoconfigure enabled globally
- Entities excluded from service container
- Doctrine mapping type: `attribute`
- API Platform defaults: stateless, `/api` route prefix, cache headers with Vary
- **Webpack Encore** (`webpack.config.js`): entry point `assets/app.js`, output to `public/build/`; add npm packages with `npm install <package>` then import in JS/CSS

## Frontend & assets

- **Webpack Encore** builds and versions CSS/JS assets; output goes to `public/build/` (gitignored)
- **Bootstrap 5** is the CSS framework for the admin panel: CSS is imported in `assets/app.js`, with additional rules in `assets/styles/app.css`
- **Font Awesome Free** (`@fortawesome/fontawesome-free`): In `assets/app.js` import both `css/fontawesome.min.css` (Icon-Definitionen) and `css/solid.min.css` (**@font-face** + `webfonts/fa-solid-900.woff2` für `fa-solid`). Ohne `solid.min.css` erscheinen keine Glyphen. Add/update packages with `npm install`/`npm update`. Nur **Font Awesome Free**; Pro ist hier nicht eingerichtet
- **Vue.js 3** (via Symfony UX): Vue controller components live in `assets/vue/controllers/` and are auto-registered via `registerVueControllerComponents()`. Use `<div data-controller="hello" data-hello-name-value="...">` in Twig to mount Vue components
- **Stimulus** (via Symfony UX): Stimulus controllers live in `assets/controllers/` and are registered via `stimulus_bootstrap.js`
- Prefer component-friendly markup and Bootstrap utility classes; avoid large blocks of inline CSS except for rare one-off cases
- **Department admin form** (`templates/admin/department/_form.html.twig`): nested `CollectionType` rows use cards with a red icon-only remove control (`btn-danger`, Font Awesome trash) in the top-right corner; dynamic rows added by the inline script must mirror the same HTML structure as Twig

## Docker

```bash
docker compose up -d       # Start MySQL 9
docker compose down        # Stop
docker compose down -v     # Stop and remove data
```

MySQL connection: `mysql://user:secret@localhost:3306/database`

## Key Decisions

- API Platform resources defined on Entity classes directly (no separate ApiResource classes yet)
- No `strict_types` declaration in app code — follow existing convention
- German UI strings — keep all user-facing text in German
- **Webpack Encore** for building and versioning assets; **Bootstrap 5** for CSS framework and UI primitives; **Font Awesome Free** for icons; **Vue.js 3** (Symfony UX) for interactive components