# AppUtils Core

Core classes and interfaces for the Application Utils ecology of libraries.
This package contains all most low-level classes and interfaces that are
interconnected and cannot be separated into their own packages.

The larger project is [mistralys/application-utils](https://github.com/Mistralys/application-utils).

## Requirements

- PHP 8.4 or higher.
- [Composer](https://getcomposer.org/) for installation and autoloading.
- Extensions: `json`, `mbstring`, `curl`, `ctype`, `libxml`, `dom`, `gd`.

## Components

### PHP classes

- `ClassHelper` - Static methods for class checking, loading and filtering.
  - `ClassRepository` - Dynamic class loading and caching.
- `PHPClassInfo` - Information about a PHP class without using reflection.

### File system

- `FileHelper` - Static method for general file system manipulations.
- `FileFinder` - Search for and filter files.
- `FileInfo` - File information and manipulation.
  - `JSONFile` - Specialized JSON file handling.
  - `PHPFile` - Specialized PHP file handling.
  - `SerializedFile` - Specialized serialized (via `serialize()`) file handling.
- `FolderInfo` - Folder information and manipulation.
- `FolderFinder` - Search for and filter folders.
- `FolderTree` - Static method to manipulate entire folder trees.
- `PathRelativizer` - Relativize paths between two folders.
- `PathsReducer` - Reduce a list of paths to the shortest possible form.
- `MimeTypes` - Database of mime types and helper methods.

### Data structures

- `ArrayDataCollection` - Type-safe associative array handling.
  - `ArrayDataObservable` - Observable version of `ArrayDataCollection` to track changes.
- `NumberInfo` - Parse numbers, access and manipulate their parts.
- `URLInfo` - Parse and manipulate URLs.
- `ThrowableInfo` - Extended `Throwable` information with serialization and unserialization.
- `VariableInfo` - Extended information on any PHP variable.

### Strings

- `HiddenConverter` - Debug pesky invisible characters.
- `OutputBuffering` - Object-oriented output buffering with exception error handling.
- `QueryParser` - Query string parser that eliminates the `parse_str` pitfalls.
- `StringBuilder` - Concatenate strings and HTML tags in many ways.
- `StringHelper` - Collection of static string manipulation methods.
- `StringMatch` - String matching and manipulation.
- `Stringable` - Interface and trait for objects that can be converted to strings.
- `TabsNormalizer` - Normalize tabs in strings.
- `Transliteration` - Convert strings to ASCII.
- `WordSplitter` - Split words in strings.
- `WordWrapper` - Wrap texts.

### HTML markup

- `HTMLHelper` - Static methods for HTML markup generation.
- `HTMLTag` - Object-oriented HTML tag creation.
- `AttributeCollection` - Object-oriented HTML attribute handling.
- `StylesCollection` - Object-oriented CSS style handling.
- `Attributable` - Interface and trait for objects that can have attributes.
- `Classable` - Interface and trait for objects that can have classes.
- `Optionable` - Interface and trait for objects that can have options.
- `Renderable` - Interface and trait for objects that can be rendered.
- `Stylable` - Interface and trait for objects that can have styles.

### Date and time

- `DateTimeHelper` - Static conversion and helper methods for date and time handling.
- `Microtime` - DateTime extension that can handle micro- and nanoseconds.
- `DateIntervalExtended` - Wrapper for the native PHP class with QoL methods.
- `DaytimeStringInfo` - Parses and validates daytime strings, e.g. `14:30`.
- `DurationConverter` - Convert date and time durations.
- `DurationStringInfo` - Parse and manipulate standardized duration strings, e.g. `1h 30m`.
- `IntervalConverter` - Convert date intervals.
- `TimeDurationCalculator` - Fill out missing values between start time, end time and duration.

### Colors

- `RGBAColor` - Class for RGB and alpha color handling and manipulation.
- `HSVColor` - Class for HSV-based color handling and manipulation.

## Companion libraries

This package is part of a larger ecology of libraries, of which it is the core component.
The following companion libraries are available:

- [application-utils](https://github.com/Mistralys/application-utils) - The main package with the full feature set.
- [application-utils-collections](https://github.com/Mistralys/application-utils-collections) - Interfaces, traits and classes for handling item collections or enums.
- [application-utils-events](https://github.com/Mistralys/application-utils-events) - Library with event handling classes, interfaces and traits.
- [application-utils-image](https://github.com/Mistralys/application-utils-image) - Image manipulation library for basic image operations and color management.
- [application-utils-result-handling](https://github.com/Mistralys/application-utils-result-handling) - Classes used to store information on the results of application operations.
- [application-framework](https://github.com/Mistralys/application-framework) - Application framework for building web applications.
- [application-localization](https://github.com/Mistralys/application-localization) - Localization and internationalization library.
- [application-datagrids](https://github.com/Mistralys/application-datagrids) - Object-oriented HTML table abstraction.

## Documentation

As a general rule, I try to document as much as possible in the code itself.
All other documentation can be found in the AppUtils wiki:

[mistralys/application-utils wiki](https://github.com/Mistralys/application-utils/wiki)
