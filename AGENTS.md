# AGENTS.md — application-utils-core

> **Operating System for AI Agents.** Read this file first and in full before touching any source code.

---

## 1. Project Manifest — Start Here!

All canonical project knowledge lives in the **Project Manifest** at:

```
docs/agents/project-manifest/
```

An agent **must** read the manifest before reading implementation code. The manifest is the authoritative source of truth. If source code contradicts the manifest, assume the code is wrong and flag it.

### Manifest Documents

| Document | Description |
|---|---|
| [README.md](docs/agents/project-manifest/README.md) | Package overview, module index, and quick orientation. |
| [tech-stack.md](docs/agents/project-manifest/tech-stack.md) | Runtime, language, PHP extensions, Composer dependencies, architectural patterns, build and test tooling. |
| [file-tree.md](docs/agents/project-manifest/file-tree.md) | Fully annotated directory structure of the project. |
| [api-surface.md](docs/agents/project-manifest/api-surface.md) | Public constructor, property, and method signatures for every class, interface, and trait, grouped by module. |
| [data-flows.md](docs/agents/project-manifest/data-flows.md) | Main interaction paths through the library (entry points → helper classes → output). |
| [constraints.md](docs/agents/project-manifest/constraints.md) | Established conventions, rules, and non-obvious gotchas found in the codebase. |

### Quick Start Workflow

Follow this sequence at the start of every agent session:

1. **Read** [docs/agents/project-manifest/README.md](docs/agents/project-manifest/README.md) — orient yourself: package purpose, version, and module map.
2. **Read** [docs/agents/project-manifest/tech-stack.md](docs/agents/project-manifest/tech-stack.md) — understand the language, dependencies, and architectural patterns before writing any code.
3. **Read** [docs/agents/project-manifest/constraints.md](docs/agents/project-manifest/constraints.md) — internalize the rules and gotchas. Violating these is the #1 source of agent errors in this codebase.
4. **Reference** [docs/agents/project-manifest/file-tree.md](docs/agents/project-manifest/file-tree.md) — look up file locations before searching the filesystem.
5. **Reference** [docs/agents/project-manifest/api-surface.md](docs/agents/project-manifest/api-surface.md) — look up method signatures before reading source files.
6. **Reference** [docs/agents/project-manifest/data-flows.md](docs/agents/project-manifest/data-flows.md) — trace usage patterns before implementing integrations or tests.
7. **Only then** — open source files in `src/` or `tests/` as needed.

---

## 2. Manifest Maintenance Rules

When you make changes to the codebase, you **must** update the corresponding manifest documents. Stale manifests break future agent sessions.

| Change Made | Documents to Update |
|---|---|
| New class or interface added | `file-tree.md`, `api-surface.md` |
| Existing class renamed or moved | `file-tree.md`, `api-surface.md` |
| Class deleted or deprecated | `file-tree.md`, `api-surface.md`, `constraints.md` (deprecation policy section) |
| New public method or constructor added | `api-surface.md` |
| Public method signature changed | `api-surface.md` |
| New Composer dependency added or removed | `tech-stack.md` |
| PHP version requirement changed | `tech-stack.md`, root `README.md` |
| New PHP extension required | `tech-stack.md` |
| Directory restructured or new module folder added | `file-tree.md` |
| New architectural pattern introduced | `tech-stack.md` |
| New coding rule or convention identified | `constraints.md` |
| New global helper function added to `src/functions.php` | `file-tree.md` (functions.php annotation), `api-surface.md` |
| New interaction flow or entry point | `data-flows.md` |
| New localization file added | `file-tree.md` |

---

## 3. Efficiency Rules — Search Smart

Do **not** scan source files to answer questions that the manifest already answers.

| Task | Do This First |
|---|---|
| Finding where a class or file is located | Check `file-tree.md` FIRST |
| Looking up a method signature or return type | Check `api-surface.md` FIRST |
| Understanding how classes interact | Check `data-flows.md` FIRST |
| Identifying the right architectural pattern to apply | Check `tech-stack.md` FIRST |
| Confirming a naming or design rule | Check `constraints.md` FIRST |
| Only after the manifest does not answer | Open source files in `src/` |

**Token efficiency rule:** The manifest is compact and complete. Reading a 250-line manifest document costs far fewer tokens than grepping through dozens of source files. Always exhaust the manifest first.

---

## 4. Failure Protocol & Decision Matrix

| Scenario | Action | Priority |
|---|---|---|
| Ambiguous requirement or unclear intent | Use the most restrictive interpretation that preserves backwards compatibility | MUST |
| Manifest and source code disagree | Trust the manifest; flag the source code discrepancy in your response | MUST |
| A manifest document is missing or empty | Flag the gap explicitly; do not invent facts about the project | MUST |
| A method or class is not found in `api-surface.md` | Search `file-tree.md` for the file, then read the source — do not assume the class does not exist | MUST |
| Tempted to use `new ClassName()` with a builder class | Check `constraints.md` Static vs. Instantiated Classes section; prefer named constructors (`::create()`, `::factory()`) | MUST |
| Adding a new class | Follow the module folder convention in `file-tree.md`; add an `Exception.php` in the module folder if the class can throw | SHOULD |
| Adding an interface or trait | Place in `src/Interfaces/` or `src/Traits/` respectively; follow the `*Interface.php` / `*Trait.php` naming pattern | MUST |
| Touching a deprecated class in `src/_deprecated/` | Do not modify deprecated classes; do not reference them in new code | MUST |
| Writing a test | Mirror the `src/` structure under `tests/AppUtilsTests/`; put fixtures in `tests/assets/` | SHOULD |
| Windows path handling needed | Use `FileHelper::detectWindowsDriveLetter()` and `FileHelper::removeWindowsDriveLetter()` | SHOULD |
| Untested code path | Add a test recommendation or `// TODO: test` comment; do not silently skip | SHOULD |
| Static analysis (PHPStan) failure | Fix the type error; do not suppress with `@phpstan-ignore` unless there is no alternative | MUST |

### Project-Specific Gotchas

- **Autoloading is `classmap`, not PSR-4.** Class file locations do not follow directory-to-namespace mapping strictly. Use `file-tree.md` or `ClassHelper::getClassSourceFile()` to locate a class — do not assume the path from the namespace.
- **`FileInfo` and `FolderInfo` use static caches.** In test code, call `FileInfo::clearCache()` / `FolderInfo::clearCache()` between tests that mutate the filesystem.
- **`RequestHelper` is not a general HTTP client.** It only sends `multipart/form-data` POST requests. Do not use it for GET requests or JSON POST bodies.
- **`t()` checks for the localization package at call time.** The function falls back to `sprintf()`. All format strings passed to `t()` must be valid `sprintf` templates.
- **`RGBAColor` setters are mutable.** `adjustBrightness()` returns a new instance, but other setters mutate in place. Do not assume immutability.
- **`NumberInfo` has an immutable variant.** Use `parseNumberImmutable()` / `NumberInfo_Immutable` when immutability is required.

---

## 5. Project Stats

| Property | Value |
|---|---|
| **Package** | `mistralys/application-utils-core` |
| **Version** | 2.4.x |
| **Language** | PHP 8.4+ |
| **Namespace root** | `AppUtils` |
| **Architecture** | Pure utility library — no framework dependency |
| **Autoloading** | Composer `classmap` (NOT PSR-4) |
| **Package manager** | Composer |
| **Test framework** | PHPUnit ≥ 12.4 |
| **Static analysis** | PHPStan ≥ 2.1 |
| **Test config** | `phpunit.xml` |
| **Entry point** | `src/functions.php` (global helpers, auto-loaded) |
| **License** | MIT |
