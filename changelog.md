## v1.0.3 - MimeType enhancements
- FileInfo: Added `getMimeType()`.
- FileInfo: Added `sendToBrowser()`.
- MimeTypes: Extensions are now case-insensitive, and work with or without dot.
- MimeTypes: Added `extensionExists()`.
- MimeTypes: Added `setBrowserCanDisplay()`.
- MimeTypes: Added `getExtensionsByMime()`.
- MimeTypes: Added `resetToDefaults()`.
- MimeTypes: Added constants for often used file mime types in `MimeTypesEnum`.
- MimeTypes: Added a dedicated test case file.

## v1.0.2 - Throwable deprecation
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
