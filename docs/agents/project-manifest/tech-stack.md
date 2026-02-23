# Tech Stack & Patterns

## Runtime & Language

| Item | Value |
|---|---|
| Language | PHP |
| Minimum PHP version | **8.4** |
| Namespace root | `AppUtils` |
| Package type | `library` (Composer) |

## Required PHP Extensions

| Extension | Purpose |
|---|---|
| `json` | JSON encoding / decoding |
| `mbstring` | Multibyte string operations |
| `curl` | HTTP requests (`RequestHelper`, `FileHelper::downloadFile`) |
| `ctype` | Character-type checking |
| `libxml` | XML parsing base |
| `dom` | DOM manipulation (XML/HTML) |
| `gd` | Image / color operations |
| `simplexml` | Simplified XML access |

## Production Dependencies (Composer)

| Package | Version | Usage |
|---|---|---|
| `scrivo/highlight.php` | `^9.18` | Syntax highlighting (`Highlighter`) — a PHP port of highlight.js; supports 185 languages; uses CSS class-based styling (`hljs` classes) instead of inline styles. |
| `neitanod/forceutf8` | `>=2.0.4` | UTF-8 normalization (`StringHelper::toUtf8`) |

## Development Dependencies

| Package | Version | Purpose |
|---|---|---|
| `phpunit/phpunit` | `>=12.4` | Unit test runner |
| `phpstan/phpstan` | `>=2.1` | Static analysis |
| `phpstan/phpstan-phpunit` | `>=2.0` | PHPStan extension for PHPUnit assertions |

## Optional Integration

| Package | Notes |
|---|---|
| `mistralys/application-localization` | If installed, the `t()` helper delegates to `\AppLocalize\t()` for translations. Without it, `t()` falls back to `sprintf()`. |

## Package Manager

- **Composer** — Dependency management and autoloading.  
- Autoloading strategy: `classmap` for all `src/` classes + explicit `files` entry for `src/functions.php` (global helper functions).

## Architectural Patterns

| Pattern | Where Used |
|---|---|
| **Static utility class** | `ClassHelper`, `FileHelper`, `StringHelper`, `ConvertHelper`, `DateTimeHelper`, `HTMLHelper`, `OutputBuffering` — all methods are `static`, classes are never instantiated. |
| **Fluent builder / method chaining** | `StringBuilder`, `AttributeCollection`, `StyleCollection`, `HTMLTag`, `RequestHelper` — setters return `$this` / `self`. |
| **Named constructor / factory** | Many classes expose `static create()` or `static factory()` methods in preference to `new`. Examples: `ArrayDataCollection::create()`, `HTMLTag::create()`, `AttributeCollection::createAuto()`, `FileInfo::factory()`, `FolderInfo::factory()`, `Microtime::createNow()`. |
| **Interface + Trait pair** | Every cross-cutting concern (Optionable, Attributable, Classable, Stylable, Renderable, Stringable) is split into a `*Interface` and a `*Trait` so consuming classes can implement the interface and pull in the default behaviour via the trait. |
| **Observable / event pattern** | `ArrayDataObservable` extends `ArrayDataCollection` to allow listeners to react to value changes. |
| **Value object** | `NumberInfo`, `URLInfo`, `RGBAColor`, `HSVColor`, `Microtime`, `VariableInfo`, `ThrowableInfo`, `DurationStringInfo`, `DaytimeStringInfo` — encapsulate a single value with rich inspection and conversion APIs. |
| **Repository / Cache** | `ClassHelper` supports a caching folder and a `ClassRepository` / `ClassRepositoryManager` for dynamic class discovery. |
| **Exception hierarchy** | Domain exceptions extend `BaseException` which extends `\RuntimeException`. Each module folder typically contains its own `Exception` class. |

## Build & Test

| Tool | Config File | Purpose |
|---|---|---|
| PHPUnit | `phpunit.xml` | Unit tests under `tests/AppUtilsTests/` |
| PHPStan | `tests/phpstan/` | Static analysis configuration |
| Composer scripts | *(none defined; run via CLI)* | `composer install`, `vendor/bin/phpunit` |

## Localization

Bundled translation files are in `localization/` (German `de_DE`, French `fr_FR`) for client-side and server-side strings. They are registered automatically via `init()` in `src/functions.php` if the `application-localization` package is present.
