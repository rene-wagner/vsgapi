# Konventionen

Dieses Dokument fasst verbindliche und empfohlene Arbeitsweisen für Code, Templates und Nutzerkommunikation im Projekt zusammen.

---

## PHP allgemein

- Im **Anwendungscode** wird **kein** `declare(strict_types=1)` verwendet (Ausnahme: **Migrationen** dürfen `strict_types` nutzen).
- Sinnvolle Features von **PHP 8.2+** (named arguments, `readonly`, Enums, …) sind erwünscht.
- **Eine Klasse pro Datei**, PSR-4, Namespace `App\`.

### Imports

- Reihenfolge der `use`-Blöcke: PHP-Standardbibliothek → Drittanbieter (ApiPlatform, Doctrine, Symfony) → `App\…`.
- **Pro Klasse ein eigener `use`-Eintrag** — keine gruppierten Imports (`use A, B`).
- Übliche Aliase: `Doctrine\ORM\Mapping as ORM`, `Symfony\Component\Validator\Constraints as Assert`.

### Typen

- **Alle** Parameter und Rückgabewerte von Methoden sind zu typisieren.
- Nullable Eigenschaften: `private ?string $email = null`.
- Setter mit fluenter API: `public function setEmail(string $email): static`.
- Getter spiegeln Nullbarkeit: `public function getEmail(): ?string`.
- `@var` / `@return` nur bei Bedarf für Generics, z. B. `/** @var list<string> */`.
- `void` bzw. `never` wo semantisch passend (z. B. Logout-Stubs).

---

## Benennung

| Element | Konvention | Beispiel |
|---------|------------|----------|
| Klassen | `PascalCase` | `UserController`, `CreateAdminCommand` |
| Methoden, Eigenschaften | `camelCase` | `getFullName()`, `$firstName` |
| Routen-Namen | `snake_case`, Modul-Präfix | `admin_user_index` |
| URL-Pfade | klein, klar | `/admin/users`, `/admin/users/{id}/edit` |
| Konsolenbefehle | `kebab-case`, Präfix `app:` | `app:create-admin` |
| Form-Optionen | `snake_case` | `is_edit`, `data_class` |
| Twig-Dateien | `snake_case.html.twig` | Partials mit `_` am Anfang |

---

## Klassenmuster

- **Controller:** erben von `AbstractController`; Abhängigkeiten in Aktionen per **Methodeninjektion**, nicht im Controller-Konstruktor.
- **Repositories:** erben von `ServiceEntityRepository<Entity>`.
- **Forms:** erben von `AbstractType`.
- **Commands:** erben von `Command`, mit `#[AsCommand]`; bei Commands/Services **Constructor Promotion** mit `private readonly`.
- Bei mehrzeiligen Parameterlisten **abschließendes Komma** nach dem letzten Parameter.

---

## Doctrine-Entitäten

- Mapping nur über Attribute: `#[ORM\Entity]`, `#[ORM\Column]`, `#[ORM\Id]`, …
- Validierung mit Symfony-Attributen, z. B. `#[Assert\NotBlank]`, `#[Assert\Email]`.
- API Platform: `#[ApiResource(...)]` mit **expliziter** Operationsliste; Serialisierungsgruppen nach Muster `{entity}:read` und `{entity}:write`.
- Eigenschaften **private** mit Getter/Setter.
- IDs: `private ?int $id = null` mit automatischer Generierung.
- Reservierte Tabellennamen in Backticks: `#[ORM\Table(name: '`user`')]`.

---

## Fehlerbehandlung und Oberfläche

- Validierung über den Symfony **Validator** (z. B. `$this->validator->validate(...)`).
- Zerstörerische Aktionen mit **CSRF**: `$this->isCsrfTokenValid(...)`.
- Rückmeldungen an Nutzer: **Flash-Messages** (`$this->addFlash('success', '…')`).
- Alle **sichtbaren Texte für Endnutzer auf Deutsch**, z. B. *„Benutzer wurde erfolgreich erstellt.“*

---

## Twig

- Admin-Templates erweitern `admin/layout.html.twig` (welches `base.html.twig` einbindet).
- Übliche Blöcke: `title`, `content`, `stylesheets`, `javascripts`.
- **Styles und Skripte** über den **Symfony AssetMapper**: `importmap('app')` im Basis-Layout; Einstieg `assets/app.js` importiert u. a. **Bootstrap 5**-CSS, **Font Awesome** `fontawesome.min.css` plus **`solid.min.css`** (lädt die Webfont für `fa-solid`), danach `assets/styles/app.css`. Kein Webpack Encore.
- **Icons:** Font Awesome (z. B. `fa-solid fa-trash`); bei rein grafischen Schaltflächen `aria-label` auf dem Button und `aria-hidden="true"` am `<i>`-Element.
- Formulare **manuell** mit `form_label`, `form_widget`, `form_errors` (nicht `form_row`).
- URLs nur über `path()` / `url()`, keine hart codierten Links.
- HTML-Attribut `lang="de"` (Basis-Layout).

---

## Sicherheit

- Anmeldung: **Formular-Login** mit CSRF.
- Admin-Pfade unter `/admin` erfordern **`ROLE_ADMIN`** (siehe `security.yaml`).
- API unter `/api` gemäß API-Platform-Konfiguration; API **stateless**, Admin **mit Session**.

---

## Symfony-Konfiguration

- Services: global **Autowiring** und **Autoconfigure**.
- Entitäten sind **keine** Dienste im Container.
- Doctrine-Mapping-Typ: **attribute**.

---

## Produkt- und Technikentscheidungen

- API-Ressourcen zunächst **auf den Entitäten**, nicht als separate API-Ressourcen-Klassen.
- **Frontend:** Symfony **AssetMapper** + `importmap.php` — kein Webpack Encore. Nach `composer install` **`symfony console importmap:install`** ausführen, damit Pakete aus der Importmap (u. a. Bootstrap, Font Awesome) unter `assets/vendor/` liegen; für Produktion/CI ggf. `symfony console asset-map:compile`.

---

## Qualitätssicherung (bei Einführung)

- **Tests:** PHPUnit über `symfony/phpunit-bridge`; Tests unter `tests/`, Namespace `App\Tests\`.
- **Analyse / Format:** z. B. `vendor/bin/phpstan analyse src/`, `vendor/bin/php-cs-fixer fix` — derzeit nicht im Projekt vorkonfiguriert.
