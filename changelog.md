## v2.3.5 - HTMLTag improvements and Duration String
- HTMLTag: Added `style()` to set and remove inline styles.
- HTMLTag: `attr()` now accepts non-string values.
- HTMLTag: Added `appendContent()` as alias for `addHTML()`.
- HTMLTag: Added `title()`.
- StyleCollection: Added possibility to remove styles by setting them to `null`.
- DurationString: Added the duration string parser class `DurationStringInfo`.
- DurationString: Added the global function `parseDurationString()`.
- DateIntervalExtended: Added `fromDurationString()`.
- DateIntervalExtended: Added `fromAuto()`.
- DaytimeString: Added the daytime string parser class `DaytimeStringInfo`.
- DaytimeString: Added the global function `parseDaytimeString()`.
- DateTimeHelper: Added constants for the number of seconds in a minute, hour, day, and week.
- TimeDurationCalculator: Added the utility class `TimeDurationCalculator`.

## v2.3.4 - FileHelper Improvements
- FileHelper: Added possibility to register custom file type classes with `ExtensionClassRegistry`.
- FileHelper: Added `EXTENSION` constants for all specialized `FileInfo` classes.
- FileHelper: Added the interfaces `FileInfoInterface` and `FolderInfoInterface`.
- Microtime: Fixed incorrect handling of the nanosecond values.

## v2.3.3 - Code quality
- Code: PHPStan type fixes, clean up to level 6.
- HTMLHelper: Added static `formatHTML()`.
- XMLHelper: Added static `formatXML()`.
- ArrayDataCollection: Updated type docs to allow mixed arrays.

## v2.3.2 - FileHelper Improvements
- Files and Folders: Added `requireModifiedDate()` with a guaranteed return value.

## v2.3.1 - URLInfo Stringable
- URLInfo: Added the Stringable interface to allow casting URLs to string.

## v2.3.0 - FileHelper Improvements (Deprecation)
- JSONFile: Made the `$pretty` parameter optional.
- JSONFile: Added `getData()` as alias for `parse()`.
- JSONFile: Added `setPrettyPrint()` to set the option separately.
- JSONFile: Added `setTrailingNewline()` to add a newline at the end of the file.
- JSONFile: Added `setEscapeSlashes()` to set whether to escape slashes in the output.
- JSONFile: Added `options()` to work with all options.
- JSONFile: Added `JSONOptions` with the possibility to set options globally.
- Optionable: Tweaked the handling of default values.
- Optionable: Added setting option default values.
- Optionable: Added `getArrayAdvanced()` for type-specific array handling.
- FileFinder: Added `getFiles()` for a wider range of type-specific methods to access files.
- ClassHelper: Added `findClassesInFolder()` to get all classes in a folder.
- ClassHelper: `requireClassExists()` can now be used interchangeably for classes, interfaces, and traits.
- ClassHelper: Added `isClassInstanceOf()`.

### Deprecations

The following FileFinder methods have been deprecated:

- `FileFinder::getAll()` > `FileFinder::getMatches()`
- `FileFinder::getFileInfos()` > `FileFinder::getFiles()->typeANY()`
- `FileFinder::getPHPFiles()` > `FileFinder::getFiles()->typePHP()`
- `FileFinder::getPHPClassNames()` > `FileFinder::getFiles()->PHPClassNames()`

## v2.2.4 - Mime Types
- FileHelper: Added the mime types for `wmv` and `mkv` files.

## v2.2.3 - ClassHelper improvements
- ClassHelper: Added `resolveClassByReference()`.
- ClassHelper: Added `getClassesInFolder()`.

## v2.2.2 - File helper improvements
- FileInfo: Added chainable `send()` as alias for using `getDownloader()`.

## v1.2.1 - File helper improvements
- FileInfo: The `factory()` method now returns specialized instances by extension (e.g. `JSONFile`).
- FileInfo: Added `getFolder()` to get the `FolderInfo` instance.
- FolderInfo: Added `getSubFiles()`.
- FolderInfo: Added `isEmpty()`.
- FolderInfo: Added `createFileFinder()`.
- FileFinder: Added `getFileInfos()` to fetch `FileInfo` instances.
- FileHelper: `getExtension()` no longer creates object instances.

## v1.2.0 - String conversions and Refactoring (Deprecation)
- Core: Refactored some classes for a more logical structure.
- ConvertHelper: Added `string2camel()`.
- ConvertHelper: Added `string2snake()`.
- ConvertHelper: Added `snake2camel()`.
- ConvertHelper: Added `removeSpecialCharacters()`.
- ConvertHelper: Added `ucFirst()`.
- ConvertHelper: Added `addWordCharacter()` in the word splitter.
- ConvertHelper: Fixed the word splitter preserving some special characters.
- StringBuilder: Changed behavior when passing zero values to methods ([#1](https://github.com/Mistralys/application-utils-core/issues/1)).

### Deprecation changes

Several classes have been deprecated. They have been moved 
to more logical places. Stubs have been left in place to make the
migration easier to be backwards compatible.

- `ConvertHelper_Date` => `DateTimeHelper`
- `ConvertHelper_DateInterval` => `DateTimeHelper\DateIntervalExtended`
- `ConvertHelper_DurationConverter` => `DateTimeHelper\DurationConverter`
- `ConvertHelper_IntervalConverter` => `DateTimeHelper\IntervalConverter`
- `ConvertHelper_TimeConverter` => `DateTimeHelper\TimeConverter`
- `ConvertHelper_String` => `StringHelper`
- `ConvertHelper_QueryParser` => `StringHelper\QueryParser`
- `ConvertHelper_StringMatch` => `StringHelper\StringMatch`
- `ConvertHelper\WordSplitter` => `StringHelper\WordSplitter`
- `ConvertHelper_WordWrapper` => `StringHelper\WordWrapper`

> NOTE: Deprecated class have been moved to `src/_deprecated` to remove
> clutter in the source folders. This has no effect on the autoloading.

## v1.1.5 - Added string conversion methods
- ConvertHelper: Added `camel2snake()`.
- ConvertHelper: Added `isStringUnicode()`.
- ConvertHelper: Added `isCharUppercase()`.

## v1.1.4 - String builder attribute support
- Classable: Classes are now sorted alphabetically by default.
- StringBuilder: Added optional attributes to all tag methods.
- StringBuilder: Added `boolYes()`.
- AttributeCollection: Added the global function `attr()` for brevity.
- AttributeCollection: Added support for parsing query strings for attributes.
- AttributeCollection: Added `setAttributeString()`.

## v1.1.3 - String builder tweak
- StringBuilder: Link parameters `$label` and `$url` now accept `StringableInterface`.

## v1.1.2 - JSHelper enhancement
- JSHelper: Added HTML tag awareness to `quoteStyle()`.

## v1.1.1 - JSHelper enhancement
- JSHelper: Added the quote style converter with `quoteStyle()`.

## v1.1.0 - Added the Type Filter helper
- TypeFilter: Added the `StrictType` and `LenientType` classes.
- Traits: Added the `RuntimePropertizable` trait and interface.

## v1.0.4 - FileHelper enhancements
- FolderInfo: Added `getSubFolders()` helper method.
- FolderInfo: Added the `$recursive` parameter to `getSize()`.
- FolderInfo: Added `getSubFile()`.
- FolderFinder: The paths are now sorted alphabetically by default.

## v1.0.3 - StringBuilder enhancements
- StringBuilder: Added `useClass()` and `useClasses()`.
- StringBuilder: Added `italic()`.
- StringBuilder: Added `useNoSpace()`.
- StringBuilder: All HTML tags now support classes via `useClass()`.

## v1.0.2 - MimeType enhancements
- FileInfo: Added `getMimeType()`.
- FileInfo: Added `sendToBrowser()`.
- MimeTypes: Extensions are now case-insensitive, and work with or without dot.
- MimeTypes: Added `extensionExists()`.
- MimeTypes: Added `setBrowserCanDisplay()`.
- MimeTypes: Added `getExtensionsByMime()`.
- MimeTypes: Added `resetToDefaults()`.
- MimeTypes: Added constants for often used file mime types in `MimeTypesEnum`.
- MimeTypes: Added a dedicated test case file.
- Microtime: Added `isDST()` in time zones to account for daylight savings time. 
- ThrowableInfo: Restored the `ConvertHelper_ThrowableInfo` class for easier deprecation.

## v1.0.1 - URLInfo fix
- URLInfo: Fixed missing CSS when calling `getHighlightCSS()`.

## v1.0.0 - Initial release
- Core: Split into its own project from the main AppUtils code base.
- UnitTests: Modernized and namespaced throughout.
- Traits: Added better named versions of the older traits and interfaces.

### Deprecation changes

- The `Interface_Stringable` has been renamed to `StringableInterface`.
- The `Interface_Optionable` has been renamed to `OptionableInterface`.
- The `Interface_Classable` has been renamed to `ClassableInterface`.
- The `ConvertHelper_ThrowableInfo` class has been renamed to `ThrowableInfo`.

For all of these interfaces and traits, the old versions are still 
available to help with the migration. They will be removed in a future
release.
