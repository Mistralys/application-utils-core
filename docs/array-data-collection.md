# ArrayDataCollection Module

A structured key-value store for associative arrays. Its primary responsibility is to eliminate repetitive `isset()` / type-cast boilerplate by providing **strict-typed read and write access** to a `string|int` → `mixed` data array. All getter methods are exception-free by design and return a safe default when a key is absent or holds the wrong type.

---

## Why this module exists

PHP associative arrays have no type contracts. Code that reads from them must defensively check for key existence, handle `null`, and coerce types — the same boilerplate repeated everywhere. `ArrayDataCollection` centralises those rules once so that consuming code can simply ask for the typed value it expects and trust the result.

The collection is **not a validator**. It will store whatever it is given. The host class is responsible for deciding whether the data is correct; the collection only guarantees that accessing a key is always safe and returns a predictable type.

---

## Core class

**`AppUtils\ArrayDataCollection`** wraps a plain PHP array. Typed getters cover all common scalar types, arrays, and date/time values. They return a zero-value or `null` rather than throwing when a key is missing or the stored value cannot be coerced. The two `require*` variants (`requireDateTime`, `requireMicrotime`) are the only deliberate exception-throwing getters — use these when the key is contractually guaranteed to be present.

The class is almost always instantiated via the named constructor `::create()`, which accepts an array, `null`, or an existing `ArrayDataCollection` (returning it unchanged). `::createFromJSON()` parses a JSON string directly into a new instance.

```php
$col = ArrayDataCollection::create(['name' => 'Alice', 'age' => 30]);
$col = ArrayDataCollection::createFromJSON('{"name":"Alice"}');
```

### DateTime and Microtime keys

Date values are persisted as formatted strings (`setDateTime`, `setMicrotime`) so they survive serialisation (e.g. to a database or JSON payload) and can be restored transparently with the matching getter. Use `getMicrotime` specifically when microsecond precision must be preserved; `getDateTime` works for ordinary timestamps and date strings.

### Collection-level operations

`mergeWith()` applies another collection's keys over the current one (target wins on collision, mutating `$this`). `combine()` does the same but returns a **new** collection, leaving both originals unchanged.

---

## Sub-array helpers

When a key's value is itself an array, two dedicated helpers provide a richer interface.

### ArrayFlavors — reading a sub-array

**`AppUtils\ArrayDataCollection\ArrayFlavors`** is returned by `$col->getArrayFlavored('key')`. It answers the common question: *"I have a raw mixed array — give me only the parts I can safely use as type X."*

Two families of methods:

- **`filter*()`** — Keeps only values that already match the target type; no coercion is applied. Use these when the data source is untrusted or heterogeneous.
- **`to*()`** — Converts compatible scalar values to the target type; incompatible values are dropped. Use these when you control the data and want to normalise it.

Methods ending with **`N`** are null-aware variants: they preserve `null` and convert empty strings to `null` rather than an empty string.

`toCollection()` and `toObservableCollection()` convert the sub-array into a full `ArrayDataCollection` instance, useful when a nested structure needs the same typed-access treatment as the parent.

### ArraySetters — mutating a sub-array

**`AppUtils\ArrayDataCollection\ArraySetters`** is returned by `$col->setArray('key')`. It solves the awkwardness of reading a sub-array, modifying it in a local variable, and writing it back — all of that is handled internally. Methods cover the common indexed operations (push, unshift, shift) and associative operations (set, remove, merge, replace), plus key sorting.

All mutating methods return the **parent `ArrayDataCollection`** so callers can chain back naturally.

---

## Observable variant: ArrayDataObservable

**`AppUtils\ArrayDataCollection\ArrayDataObservable`** extends `ArrayDataCollection` with a **reactive observer layer**. Use it wherever the host code needs to automatically react to data changes — for example, to mark an object dirty, trigger re-serialisation, or propagate updates to a UI — without polling or wrapping every write call manually.

Four observer hooks are available: collection-level (`onCollectionChanged`), key-level (`onKeyChanged`), and the more granular `onKeyAdded` / `onKeyRemoved`. Each call returns an integer ID so individual observers can be removed later without clearing all of them.

### Example

```php
$obs = ArrayDataObservable::create(['status' => 'draft']);

$id = $obs->onCollectionChanged(fn($col) => save($col->getData()));

$obs->setKey('status', 'published'); // triggers the observer above

$obs->removeObserver($id); // unregisters just this one
$obs->clearObservers();    // unregisters all observers
```

### Change detection

The observable normalises values before comparing old and new (empty strings and arrays are treated as `null`; numerics are cast to strings) to avoid spurious events when semantically identical values are written repeatedly. `clearKeys()` also batches all removal events into a single `onCollectionChanged` notification rather than firing once per key.

---

## Exceptions

`ArrayDataCollectionException` (extending `AppUtils\BaseException`) is thrown only by `requireDateTime()` and `requireMicrotime()` when a key is absent or unparseable. All other methods return silent defaults.

---

## Design notes

- **Exception-free by default.** Getters never throw; use `require*` variants only when the key _must_ be present.
- **No validation responsibility.** The collection stores whatever is given to it. Validation is the host class's concern.
- **`setKey()` is the single write gate.** Every setter — bulk, helper, or subclass — ultimately calls `setKey()`. Overriding it in a subclass (as `ArrayDataObservable` does) intercepts all writes without any extra plumbing.
- **Autoloading is `classmap`.** File paths do not follow namespace conventions. Use `ClassHelper::getClassSourceFile()` when locating class files programmatically.
