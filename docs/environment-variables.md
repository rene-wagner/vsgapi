# Umgebungsvariablen

Symfony lädt Konfigurationswerte aus Umgebungsvariablen und aus `.env`-Dateien. **Echte** Umgebungsvariablen des Betriebssystems überschreiben Werte aus Dateien.

## Ladereihenfolge (vereinfacht)

Übliche Reihenfolge (später überschreibt früher), siehe Kommentarblock in `.env`:

1. **`.env`** — Standardwerte für alle Umgebungen (versioniert, keine Produktions-Geheimnisse).
2. **`.env.local`** — lokale, nicht versionierte Überschreibungen (optional).
3. **`.env.<APP_ENV>`** — umgebungsspezifische Defaults, z. B. **`.env.dev`** für `APP_ENV=dev` oder **`.env.prod`** für `APP_ENV=prod` (Symfony-Konvention: Punkt vor `dev`/`prod`, nicht z. B. `.env-prod`).
4. **`.env.<APP_ENV>.local`** — lokale Überschreibungen pro Umgebung (optional, nicht versioniert).

`APP_ENV` steht in `.env` und bestimmt, welche Datei aus Schritt 3 geladen wird.

Für Production kann zusätzlich `composer dump-env prod` genutzt werden, um eine zusammengeführte `.env.local.php` zu erzeugen (siehe [Symfony-Dokumentation zu Secrets](https://symfony.com/doc/current/configuration/secrets.html)).

---

## Übersicht der Variablen

Die folgenden Variablen werden in der Konfiguration unter `config/packages/` referenziert. Default-Werte stehen in **`.env`** (und können in **`.env.dev`** bzw. **`.env.prod`** überschrieben werden).

| Variable | Verwendung | Default in `.env` (Beispiel) |
|----------|------------|------------------------------|
| `APP_ENV` | Laufzeitumgebung (`dev`, `prod`, `test`, …); steuert u. a. Cache und geladene `.env.*`-Datei | `dev` |
| `APP_SECRET` | Geheimer Schlüssel für CSRF, Sessions, signierte URLs etc. — **in Production durch sicheren, zufälligen Wert ersetzen** | Platzhalter in `.env` |
| `DEFAULT_URI` | Basis-URL für URL-Generierung außerhalb von HTTP (z. B. CLI), siehe `config/packages/routing.yaml` | `http://localhost` |
| `DATABASE_URL` | Doctrine-DBAL-Verbindung (MySQL), siehe `config/packages/doctrine.yaml` | `mysql://user:secret:@localhost:3306/database?serverVersion=9.6&charset=utf8mb4` |

### Nur in bestimmten Kontexten

| Variable | Verwendung |
|----------|------------|
| `TEST_TOKEN` | Optional: Suffix für Test-Datenbanknamen bei parallelen Tests (`when@test` in `doctrine.yaml`). Wird typischerweise von Tools wie ParaTest gesetzt, nicht zwingend in `.env`. |

---

## Dateien im Projekt

| Datei | Rolle |
|-------|--------|
| `.env` | Zentrale Default-Werte (im Repo). |
| `.env.dev` | Overrides für Entwicklung (`APP_ENV=dev`). |
| `.env.prod` | Overrides für Production (`APP_ENV=prod`) — anlegen, sobald produktive Werte benötigt werden; nicht mit echten Secrets im öffentlichen Repo committen, wenn das Repository nicht privat ist. |
| `.env.local` / `.env.dev.local` / `.env.prod.local` | Lokal, nicht versioniert (siehe `.gitignore`). |

Aktuell existiert im Repository u. a. `.env` und `.env.dev`; eine `.env.prod` kann bei Bedarf ergänzt werden.

---

## Verweise in der Konfiguration

- `APP_SECRET` → `config/packages/framework.yaml` (`secret`)
- `DATABASE_URL` → `config/packages/doctrine.yaml` (`dbal.url`, mit `resolve:`)
- `DEFAULT_URI` → `config/packages/routing.yaml` (`default_uri`)
