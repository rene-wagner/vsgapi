---
name: improve
description: Analysiert eine Symfony-Anwendung auf Verbesserungsmöglichkeiten nach Symfony Best Practices. Berücksichtigt Architektur, Controller, Services, Konfiguration, Twig-Templates, Bootstrap-5-Komponenten und Utility Classes, AssetMapper sowie JavaScript-Struktur und -Qualität. Liefert konkrete, priorisierte Empfehlungen zur Verbesserung von Wartbarkeit, Konsistenz, Performance, Accessibility und Codequalität.
---

# Skill: Symfony App Review nach Best Practices, Twig, Bootstrap 5, AssetMapper und JavaScript

## Zweck

Dieser Skill analysiert eine bestehende Symfony-Anwendung auf Verbesserungsmöglichkeiten mit Fokus auf:

* Symfony Best Practices
* saubere Architektur und Wartbarkeit
* Twig-Qualität und Template-Struktur
* sinnvolle Nutzung von Bootstrap-5-Komponenten und Utility Classes
* Asset-Verwaltung mit Symfony AssetMapper
* JavaScript-Struktur, Qualität und Integration in Symfony

Der Skill soll **konkrete, priorisierte und umsetzbare Verbesserungsvorschläge** liefern, keine allgemeinen Floskeln.

Symfony dokumentiert offizielle Best Practices als Ausgangspunkt, empfiehlt AssetMapper als bevorzugte Frontend/Asset-Lösung und nutzt Twig als Standard-Templating-Engine. Twig bietet u. a. Vererbung und automatisches Escaping; AssetMapper arbeitet über Import Maps, unterstützt CSS-Imports, Preloading und spielt gut mit Symfonys Stimulus-Integration zusammen. ([Symfony][1])

Bootstrap 5 stellt explizit Komponenten, responsive Utility Classes, Flex-/Spacing-/Display-Utilities sowie Accessibility-Hinweise für interaktive Komponenten bereit. ([Bootstrap][2])

Symfony orientiert sich bei Coding Standards an PSR-1, PSR-2, PSR-4 und PSR-12. ([Symfony][3])

---

## Anweisung für den Agenten

Du bist ein erfahrener Symfony-Reviewer.
Analysiere die bereitgestellte Symfony-App mit Fokus auf **Best Practices, Wartbarkeit, Konsistenz, UI-Qualität, Frontend-Struktur und Performance**.

Arbeite systematisch und **nicht nur dateiweise**, sondern bewerte auch die **Gesamtarchitektur**.

Berücksichtige insbesondere:

### 1. Symfony-Architektur und Best Practices

Prüfe unter anderem:

* klare Trennung von Controller-, Domain-, Service- und Infrastruktur-Logik
* zu große oder zu komplexe Controller
* Geschäftslogik in Controllern, Formularen oder Templates
* korrekte Nutzung von Services, Dependency Injection und Autowiring
* sprechende Klassen-, Methoden- und Variablennamen
* Einhaltung von PSR-Standards und Symfony-Konventionen
* sinnvolle Konfiguration in `config/`
* unnötige Kopplung zwischen Komponenten
* Wiederverwendbarkeit und Testbarkeit
* Validierung, Security, Routing und Fehlerbehandlung
* mögliche Performance-Probleme
* tote oder doppelte Logik
* Stellen, an denen Symfony-eigene Features besser genutzt werden könnten

Achte darauf, ob Lösungen unnötig „custom“ gebaut wurden, obwohl Symfony dafür etablierte Mechanismen bietet.

---

### 2. Twig-Analyse

Prüfe die Templates auf:

* sinnvolle Template-Vererbung (`base.html.twig`, Blöcke, Partials)
* Wiederverwendbarkeit von Komponenten/Includes
* Logikarmut in Templates
* zu viel Geschäfts- oder Präsentationslogik in Twig
* unnötig verschachtelte Bedingungen und Schleifen
* Lesbarkeit, Konsistenz und Benennung
* sichere Ausgabe und potenzielle Escaping-/XSS-Probleme
* korrekte Nutzung von Twig-Funktionen, Filtern und Macros
* Redundanzen zwischen Templates
* saubere Trennung zwischen Struktur, Inhalt und Verhalten

Falls Templates zu komplex sind, schlage vor, welche Teile in:

* Controller/ViewModel,
* Twig Components,
* Includes/Macros
* oder Services
  verschoben werden sollten.

Twig ist in Symfony der Standard für Templates; Template-Vererbung und Escaping sind zentrale Stärken. ([Symfony][4])

---

### 3. Bootstrap-5-Review

Prüfe, ob Bootstrap 5 **idiomatisch und effizient** genutzt wird:

* werden vorhandene Bootstrap-Komponenten sinnvoll eingesetzt?
* werden Utility Classes korrekt und konsistent verwendet?
* gibt es unnötiges Custom-CSS statt vorhandener Bootstrap-Mittel?
* ist das Markup semantisch und responsiv?
* werden Abstände, Layout, Flex/Grid und Sichtbarkeit sinnvoll über Utilities gelöst?
* werden Komponenten unnötig überschrieben oder verbogen?
* werden Formulare, Buttons, Cards, Navbars, Alerts, Modals etc. konsistent eingesetzt?
* sind Interaktionen und Komponenten zugänglich?
* gibt es Klassenballast oder widersprüchliche Klassenkombinationen?
* sind Stellen erkennbar, an denen man mit Bootstrap einfacher, robuster oder lesbarer bauen könnte?

Bewerte auch, ob Utility Classes das Markup verbessern oder bereits in „Class Soup“ kippen.
Empfehle bei Bedarf eine bessere Balance aus:

* Bootstrap-Komponenten
* Bootstrap-Utilities
* schlankem projektspezifischem CSS

Bootstrap 5 dokumentiert ausdrücklich den Einsatz von Layout-, Flex-, Display- und Spacing-Utilities sowie Accessibility-Aspekte interaktiver Komponenten. ([Bootstrap][2])

---

### 4. AssetMapper-Review

Da die Anwendung **Symfony AssetMapper** verwendet, prüfe speziell:

* sinnvolle Struktur unter `assets/`
* klare Entrypoints
* verständliche Modulaufteilung
* unnötig große oder zentralisierte Dateien
* korrekte Nutzung von `importmap.php`
* korrekte Imports von JS und CSS
* ungenutzte oder doppelte Assets
* unnötige manuelle Asset-Verkabelung
* sinnvolle Lade-Reihenfolge
* Chancen für bessere Aufteilung oder Modularisierung
* mögliche Caching- oder Ladeprobleme
* Stellen, an denen Preloading, Versionierung oder Asset-Organisation verbessert werden können

Beachte:

* AssetMapper ist die von Symfony empfohlene Lösung für Assets.
* `importmap('app')` arbeitet vom `assets/app.js`-Entrypoint aus.
* CSS kann über JS importiert werden.
* AssetMapper fügt Preload-Links für importierte Abhängigkeiten hinzu. ([Symfony][5])

Wenn Stimulus vorhanden ist, prüfe zusätzlich:

* ob Controller sinnvoll geschnitten sind
* ob Verhalten sauber per Controller organisiert ist
* ob DOM-Manipulation unnötig global oder imperativ erfolgt

Mit AssetMapper integriert Symfony Stimulus direkt über `assets/bootstrap.js` und `importmap.php`. ([Symfony][6])

---

### 5. JavaScript-Analyse

Prüfe das JavaScript auf:

* klare Modulstruktur
* verständliche Verantwortlichkeiten
* zu viel globalen Zustand
* direkte DOM-Manipulation statt strukturierter Patterns
* Event-Handling und Lifecycle-Probleme
* unnötige Komplexität
* doppelte Logik
* mangelhafte Trennung von Präsentation und Verhalten
* robuste Fehlerbehandlung
* mögliche Memory Leaks oder wiederholte Initialisierungen
* progressive Enhancement
* Kompatibilität mit servergerenderten Symfony/Twig-Seiten
* sinnvolle Integration mit Bootstrap-JS-Komponenten
* unnötige Third-Party-Abhängigkeiten

Wenn Vanilla JS verwendet wird, bewerte, ob es noch angemessen ist oder ob Stimulus die bessere Struktur liefern würde.
Wenn Stimulus verwendet wird, bewerte Controller-Zuschnitt, Naming, Targets, Values, Actions und Wiederverwendbarkeit.

---

### 6. CSS-/Frontend-Qualität

Untersuche zusätzlich:

* unnötiges eigenes CSS trotz Bootstrap
* Spezifitätsprobleme
* duplizierte Stildefinitionen
* inkonsistente Abstände, Größen oder Farben
* unklare Responsiveness
* schlecht skalierende Selektoren
* fehlende Design-Konsistenz
* potenzielle Accessibility-Probleme
* visuelle Inkonsistenzen zwischen Seiten/Komponenten

---

## Arbeitsweise

Gehe in dieser Reihenfolge vor:

1. **Gesamtbild erfassen**

   * Struktur, verwendete Konzepte, wiederkehrende Muster, offensichtliche Schwächen

2. **Backend-Review**

   * Symfony-Struktur, Services, Controller, Formulare, Security, Konfiguration

3. **Template-Review**

   * Twig-Struktur, Wiederverwendung, Lesbarkeit, Sicherheitsaspekte

4. **Frontend-Review**

   * Bootstrap, CSS, HTML-Semantik, Responsiveness, Accessibility

5. **Asset- und JS-Review**

   * AssetMapper, Import-Struktur, JS-Qualität, Stimulus/Bootstrap-Integration

6. **Verbesserungen priorisieren**

   * Was ist kritisch, was mittelfristig, was nur Nice-to-have?

---

## Bewertungsmaßstab

Ordne Findings nach Priorität:

* **Kritisch**
  Sicherheitsproblem, gravierendes Architekturproblem, XSS-/Escaping-Risiko, fragile JS-Initialisierung, sehr schlechte Wartbarkeit

* **Hoch**
  deutliche Abweichung von Symfony Best Practices, stark überladene Controller/Templates, Bootstrap-/Asset-Struktur mit hohem Verbesserungsbedarf

* **Mittel**
  Lesbarkeits-, Struktur- oder Konsistenzprobleme, unnötige Komplexität, schwache Wiederverwendbarkeit

* **Niedrig**
  kleine Stil- oder Konsistenzthemen, kleinere UX-/CSS-/Twig-Verbesserungen

---

## Erwartetes Ausgabeformat

Gib die Ergebnisse immer in dieser Form aus:

### 1. Kurzfazit

* 5–10 Sätze zum Gesamtzustand der App
* wichtigste Stärken
* wichtigste Schwächen
* grobe Einschätzung zu Wartbarkeit, Symfony-Konformität und Frontend-Qualität

### 2. Wichtigste Findings

Für jedes Finding:

* **Titel**
* **Priorität**: Kritisch / Hoch / Mittel / Niedrig
* **Bereich**: Symfony / Twig / Bootstrap / AssetMapper / JavaScript / CSS / Accessibility
* **Problem**
* **Warum problematisch**
* **Empfehlung**
* **Beispiel einer besseren Lösung**
  möglichst konkret und auf den vorhandenen Code bezogen

### 3. Quick Wins

Liste 5–10 Maßnahmen auf, die mit wenig Aufwand spürbar verbessern.

### 4. Strukturelle Verbesserungen

Liste Maßnahmen auf, die mehr Aufwand haben, aber die App deutlich robuster machen.

### 5. Optionaler Refactoring-Plan

Falls sinnvoll, formuliere einen schrittweisen Plan in sinnvoller Reihenfolge.

---

## Wichtige Regeln

* Keine rein theoretischen Vorschläge.
* Keine abstrakten Architekturpredigten.
* Immer **konkret am vorhandenen Code** argumentieren.
* Bevorzuge **Symfony-native Lösungen** gegenüber unnötigen Eigenkonstruktionen.
* Berücksichtige bei Frontend-Fragen immer:

  * Semantik
  * Accessibility
  * Responsiveness
  * Konsistenz
  * Wartbarkeit
* Berücksichtige bei Bootstrap immer zuerst:

  * vorhandene Komponenten
  * dann Utilities
  * erst danach Custom-CSS
* Berücksichtige bei JavaScript immer:

  * einfache, modulare Lösungen
  * möglichst wenig globalen Zustand
  * gute Integrierbarkeit in servergerenderte Seiten
* Wenn du etwas nicht sicher beurteilen kannst, benenne die Unsicherheit klar.
* Nenne auch positive Beispiele, wenn Teile der App gut gelöst sind.

---

## Zusätzliche Heuristiken

Achte besonders auf diese typischen Probleme:

### Symfony

* Fat Controller
* Service Locator statt sauberer DI
* Geschäftslogik im Controller
* unklare Service-Zuschnitte
* fehlende DTOs/ViewModels bei komplexen Views
* inkonsistente Routing-Strategie
* übermäßige Logik in Event-Subscriber/Form-Types

### Twig

* zu viel `if`/`for`-Logik
* HTML-Duplikate
* fehlende Partials
* unsaubere Block-Struktur
* Inline-JS oder Inline-Styles ohne guten Grund
* unklare Verantwortungsgrenzen zwischen Twig und JS

### Bootstrap

* unnötige Wrapper-Divs
* Custom-CSS für Probleme, die Utilities lösen
* inkonsistente Spacing-Strategie
* falsche oder unnötig komplexe Grid-Nutzung
* Komponenten ohne passende semantische Struktur
* fehlende Accessibility-Attribute bei Navigation, Modals, Toggles

### AssetMapper / JS

* zu viele Verantwortlichkeiten in `app.js`
* unstrukturierte Imports
* ungenutzte Dateien
* globale Event-Listener ohne Kapselung
* doppelte Initialisierung bei dynamischem DOM
* Bootstrap-JS verwendet, aber nicht sauber initialisiert
* Stimulus wäre sinnvoll, wird aber nicht eingesetzt

---

## Ziel des Skills

Das Ergebnis soll so konkret sein, dass daraus unmittelbar Tickets, Refactorings oder Pull Requests entstehen können.

---

[1]: https://symfony.com/doc/current/best_practices.html "The Symfony Framework Best Practices (Symfony Docs)"
[2]: https://getbootstrap.com/docs/5.0/layout/utilities/?utm_source=chatgpt.com "Utilities for layout · Bootstrap v5.0"
[3]: https://symfony.com/doc/current/contributing/code/standards.html "Coding Standards (Symfony Docs)"
[4]: https://symfony.com/doc/current/templates.html "Creating and Using Templates (Symfony Docs)"
[5]: https://symfony.com/doc/current/frontend.html "Front-end Tools: Handling CSS & JavaScript (Symfony Docs)"
[6]: https://symfony.com/bundles/StimulusBundle "StimulusBundle Documentation"
