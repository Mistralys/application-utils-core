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
