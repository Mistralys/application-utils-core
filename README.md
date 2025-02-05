# AppUtils Core

Core classes and interfaces for the Application Utils ecology of libraries.
This package contains all most low-level classes and interfaces that are
interconnected and cannot be separated into their own packages.

The larger project is [mistralys/application-utils](https://github.com/Mistralys/application-utils).

## Components

### PHP classes

- `ClassHelper` - Static methods for class checking, loading and filtering.
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

## Documentation

As a general rule, I try to document as much as possible in the code itself.
All other documentation can be found in the AppUtils wiki:

[mistralys/application-utils wiki](https://github.com/Mistralys/application-utils/wiki)
