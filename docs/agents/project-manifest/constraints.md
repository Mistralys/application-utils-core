# Constraints & Conventions

Rules, established patterns, and non-obvious gotchas found in `application-utils-core`.

---

## Language & Environment

| Rule | Detail |
|---|---|
| **Minimum PHP 8.4** | Enforced in `composer.json` (`"php": ">=8.4"`). Union types, named arguments, readonly properties, and other 8.x features are used freely. |
| **`declare(strict_types=1)`** | Present in virtually every source file. All method parameters and return types are strictly enforced. |
| **All `src/` classes use classmap autoloading** | Not PSR-4. The Composer `autoload.classmap` points to `src/`. Class names may not follow directory names exactly; use `ClassHelper::getClassSourceFile()` to locate a class. |
| **`src/functions.php` is always auto-loaded** | Registered in the Composer `files` autoload list. Global functions in the `AppUtils` namespace (e.g. `parseURL()`, `sb()`, `t()`) are available everywhere without an explicit `use`. |

---

## Naming Conventions

| Pattern | Example |
|---|---|
| Static utility classes are suffixed with nothing (no `Helper` suffix required) | `StringHelper`, `ClassHelper`, `FileHelper`, `DateTimeHelper` — but also `Highlighter`, `Transliteration`, `OutputBuffering` |
| Sub-utility classes inside a module folder are prefixed with their parent's short name and an underscore | `ConvertHelper_ByteConverter`, `ConvertHelper_StorageSizeEnum`, `RequestHelper_Response` |
| Interface files live in `src/Interfaces/` and are named `*Interface.php` | `RenderableInterface`, `StringableInterface`, `OptionableInterface` |
| Trait files live in `src/Traits/` and are named `*Trait.php` | `RenderableTrait`, `OptionableTrait`, `ClassableTrait` |
| Exception classes per module are named `Exception.php` inside their folder OR `*Exception.php` at the root level | `src/FileHelper/Exception.php`, `src/URLInfo/URLException.php`, `src/BaseException.php` |
| Deprecated classes live in `src/_deprecated/` | Do not use or reference them in new code. They are kept solely for backwards compatibility. |

---

## Design Rules

### Static vs. Instantiated Classes

- Classes that are **always static** (never instantiated): `ClassHelper`, `FileHelper`, `StringHelper`, `ConvertHelper`, `DateTimeHelper`, `HTMLHelper`, `OutputBuffering`. Do not instantiate these.
- Classes with **both static factory methods and instance methods**: `AttributeCollection`, `StyleCollection`, `HTMLTag`, `ArrayDataCollection`, `URLInfo`, `RGBAColor`, `Microtime`, `ThrowableInfo`, `VariableInfo`, `NumberInfo`. Always prefer the named constructor (`::create()`, `::factory()`, `::createNow()`, etc.) over `new` where one is provided.

### Fluent / Method-Chaining

All builder classes (`StringBuilder`, `AttributeCollection`, `StyleCollection`, `HTMLTag`, `RequestHelper`, `ArrayDataCollection`, `NumberInfo`) return `$this` or `self` from every setter. Chain calls freely.

### Value Objects are not Immutable by Default

Most value objects (`RGBAColor`, `NumberInfo`, `URLInfo`) are **mutable**. Where immutability is needed, use `NumberInfo_Immutable` (via `parseNumberImmutable()`). `adjustBrightness()` on `RGBAColor` returns a **new** instance, making it effectively immutable for that operation, but the base setters mutate in place.

### Optionable / Renderable Pattern

- Any class that needs key/value options should implement `OptionableInterface` and use `OptionableTrait`.  
- Any class that produces HTML output should implement `RenderableInterface` and use `RenderableTrait`. The trait provides `display()` (echoes `render()`) and `__toString()` (delegates to `render()`).

---

## File System Rules

- **All file I/O paths accept three forms:** `string`, `PathInfoInterface` (i.e. `FileInfo`/`FolderInfo`), or `SplFileInfo`. Helper methods accepting paths are typed as `string|PathInfoInterface|SplFileInfo`.
- **`FileInfo` and `FolderInfo` use an internal static cache.** Call `FileInfo::clearCache()` / `FolderInfo::clearCache()` in tests or when the filesystem changes between calls in the same request.
- **`FolderInfo::addSubFile()`** is the preferred way to create new files; `FileHelper::saveJSON()` is deprecated in its favour.
- **Path resolution:** Paths containing `.` or `..` segments must be resolved with `FileHelper::resolvePathDots()`. The library does not auto-canonicalize paths.
- **Windows paths:** Windows drive letters in paths may interfere with some operations. Use `FileHelper::detectWindowsDriveLetter()` and `FileHelper::removeWindowsDriveLetter()` where cross-platform path handling is required.

---

## HTTP / cURL

- `RequestHelper` **sends multipart/form-data POST requests** only. It is not a general-purpose HTTP client.
- SSL checks can be disabled via `disableSSLChecks()`, but this should only be used for local/development environments.
- Bearer token detection (`RequestHelper::getBearerToken()`) reads the `Authorization` HTTP header from the current request environment, not from a `RequestHelper` instance.

---

## Localization / Translation

- The `t()` helper function checks for `\AppLocalize\t()` **at call time** (not at boot). If the localization package is installed later in the same request, `t()` will delegate to it correctly.
- Translation files in `localization/` are registered with the localization system only when `\AppLocalize\Localization` class exists (checked in `init()`).
- Without the localization package, `t()` falls back to `sprintf()`. Ensure format strings are always valid `sprintf` templates.

---

## Color API

- **Channel values are 0–255 for RGB and 0–1 for alpha** (stored in `ColorChannel` objects; the specific range depends on the channel type).
- Prefer `ColorFactory` (static) for constructing `RGBAColor` instances from external input (HEX strings, CSS strings, arrays). Direct `new RGBAColor()` construction requires pre-built `ColorChannel` objects.
- `ColorPresets` provides named colors (e.g. `'red'`, `'blue'`). `ColorFactory::createPreset('red')` is the entry point.

---

## Testing Conventions

- Tests live under `tests/AppUtilsTests/`, mirroring the `src/` structure.
- Shared test helper classes live under `tests/AppUtilsTestClasses/` and are autoloaded via Composer `autoload-dev.classmap`.
- Test fixtures (JSON, XML, image files) live under `tests/assets/`.
- The active test configuration is `tests/config.php`; copy `config.dist.php` to create it.

---

## Deprecation Policy

- Deprecated classes are **moved** to `src/_deprecated/` with their original file names preserved for autoloading compatibility. They are not removed immediately.
- Deprecated methods carry a `@deprecated` PHPDoc tag indicating the replacement.
- The changelog records each deprecation under a "Deprecations" subheading in its version entry.

---

## Versioning

- The package follows **semantic versioning** loosely (major.minor.patch).
- Breaking changes are rare and documented in `changelog.md`.
- The minimum stability is `dev` (in `composer.json`) but `prefer-stable: true` ensures stable releases are preferred.
