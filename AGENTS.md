# AGENTS.md — VSG API

## Project Overview

Symfony 7.2 API project with API Platform 4, Doctrine ORM 3, and a Twig-based admin panel.
PHP 8.2+ required. MySQL 9 database via Docker Compose. German-language UI.

## Architecture

```
src/
  ApiResource/     # API Platform resource classes (empty, entities used directly for now)
  Command/         # Symfony console commands
  Controller/      # HTTP controllers (Admin/ subdirectory for admin panel)
  Entity/          # Doctrine ORM entities with API Platform attributes
  Form/            # Symfony form types
  Repository/      # Doctrine repositories
config/            # Symfony YAML configuration
migrations/        # Doctrine migrations
templates/         # Twig templates (admin/ with layout.html.twig base)
```

API Platform resources are defined via PHP attributes directly on Entity classes.
Admin panel controllers live under `App\Controller\Admin`, standard controllers in `App\Controller`.
Templates follow `templates/{module}/{entity}/{action}.html.twig` — partials prefixed with `_`.

## Build & Run Commands

```bash
# Install dependencies
composer install

# Start database
docker compose up -d

# Run migrations
php bin/console doctrine:migrations:migrate

# Clear cache
php bin/console cache:clear

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
- Blocks: `{% block title %}`, `{% block content %}`, `{% block stylesheets %}`
- CSS is inline in `base.html.twig` — no external CSS framework
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
- Inline CSS in base template — no build pipeline or asset bundler
