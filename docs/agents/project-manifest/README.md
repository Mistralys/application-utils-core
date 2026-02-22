# Project Manifest — application-utils-core

**Package:** `mistralys/application-utils-core`  
**Version:** 2.4.x  
**Description:** Drop-in utilities for PHP applications. Core lowest-level classes and interfaces of the Application Utils ecology.

This manifest is the canonical "Source of Truth" for AI agent sessions. It describes the codebase without reproducing implementation logic.

---

## Table of Contents

| Document | Description |
|---|---|
| [tech-stack.md](tech-stack.md) | Runtime, language, frameworks, libraries, architectural patterns, build and test tooling. |
| [file-tree.md](file-tree.md) | Annotated directory structure of the project. |
| [api-surface.md](api-surface.md) | Public constructor, property, and method signatures for every class, interface, and trait. Grouped by module. |
| [data-flows.md](data-flows.md) | Main interaction paths through the library (entry points → helper classes → output). |
| [constraints.md](constraints.md) | Established conventions, rules, and non-obvious gotchas found in the codebase. |

---

## Quick Overview

`application-utils-core` is a **pure PHP utility library** (no framework dependency) organized into thematic modules:

- **Class utilities** — `ClassHelper`, `ClassRepository`
- **File system** — `FileHelper`, `FileInfo`, `FolderInfo`, `FileFinder`, `FolderFinder`, `JSONFile`, `PHPFile`, `SerializedFile`, `MimeTypes`
- **Data structures** — `ArrayDataCollection`, `NumberInfo`, `URLInfo`, `ThrowableInfo`, `VariableInfo`
- **Strings** — `StringBuilder`, `StringHelper`, `ConvertHelper`, `OutputBuffering`, `Transliteration`, `HiddenConverter`, `QueryParser`, `WordWrapper`, `WordSplitter`, `TabsNormalizer`
- **HTML markup** — `HTMLHelper`, `HTMLTag`, `AttributeCollection`, `StyleCollection`
- **Date & time** — `DateTimeHelper`, `Microtime`, `DateIntervalExtended`, `DurationStringInfo`, `DaytimeStringInfo`, `DurationConverter`, `IntervalConverter`, `TimeDurationCalculator`
- **Colors** — `RGBAColor`, `HSVColor`
- **Interfaces & traits** — `StringableInterface`, `RenderableInterface`, `OptionableInterface/Trait`, `AttributableTrait`, `ClassableTrait`, `StylableTrait`, `RenderableTrait`
- **Global helper functions** — `parseVariable()`, `parseThrowable()`, `parseURL()`, `parseNumber()`, `sb()`, `attr()`, `t()`, and more (all in `AppUtils` namespace, auto-loaded via `src/functions.php`)
