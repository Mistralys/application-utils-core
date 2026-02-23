# File Tree

Annotated directory structure of `application-utils-core`. Auto-generated and vendored folders are collapsed.

```
application-utils-core/
│
├── composer.json               Package manifest (dependencies, autoload config)
├── phpunit.xml                 PHPUnit test configuration
├── changelog.md                Version-by-version change log
├── README.md                   Public documentation entry point
├── LICENSE                     MIT license
│
├── css/
│   └── urlinfo-highlight.css   CSS for URLInfo syntax highlighting output
│
├── localization/               Translation files (de_DE, fr_FR) for client/server strings
│   ├── composer.json           Localization subpackage manifest
│   ├── de_DE-application-utils-client.ini
│   ├── de_DE-application-utils-server.ini
│   ├── fr_FR-application-utils-client.ini
│   ├── fr_FR-application-utils-server.ini
│   └── index.php               Security stub (prevents direct folder browsing)
│
├── src/                        All library source code (namespace: AppUtils)
│   │
│   ├── functions.php           Global helper functions auto-loaded by Composer:
│   │                           parseVariable(), parseThrowable(), restoreThrowable(),
│   │                           parseURL(), parseNumber(), parseNumberImmutable(),
│   │                           parseDurationString(), parseDaytimeString(),
│   │                           parseInterval(), sb(), attr(), t(),
│   │                           array_remove_values(), init()
│   │
│   ├── ArrayDataCollection/    ArrayDataCollection module
│   │   ├── ArrayDataCollection.php       Type-safe associative array collection
│   │   ├── ArrayDataCollectionException.php
│   │   ├── ArrayDataObservable.php       Observable variant (change listeners)
│   │   ├── ArrayFlavors.php              Array filtering/conversion options builder
│   │   └── ArraySetters.php              Helper for modifying array-type keys
│   │
│   ├── AttributeCollection/    HTML attribute builder module
│   │   ├── AttributeCollection.php   OO HTML attribute builder (fluent)
│   │   ├── AttributesRenderer.php
│   │   └── Filtering.php
│   │
│   ├── ClassHelper/            ClassHelper module (static class reflection and loading)
│   │   ├── ClassHelper.php         Static class reflection and loading utilities
│   │   ├── BaseClassHelperException.php
│   │   ├── ClassLoaderNotFoundException.php
│   │   ├── ClassNotExistsException.php
│   │   ├── ClassNotImplementsException.php
│   │   └── Repository/             ClassRepository and ClassRepositoryManager
│   │
│   ├── ConvertHelper/          String/data conversion utilities
│   │   ├── ConvertHelper.php       Static string/data conversion utilities (legacy facade)
│   │   ├── Array.php               Array conversion helpers
│   │   ├── Bool.php                Boolean conversion helpers
│   │   ├── ByteConverter.php       Byte ↔ human-readable size conversion
│   │   ├── Comparator.php          String comparison
│   │   ├── ControlCharacters.php   Control character utilities
│   │   ├── EOL.php                 End-of-line detection and normalization
│   │   ├── Exception.php
│   │   ├── JSONConverter.php
│   │   ├── JSONConverter/          JSON encode/decode helpers
│   │   ├── SizeNotation.php        Human-readable size notation parser
│   │   ├── StorageSizeEnum.php
│   │   ├── StorageSizeEnum/        Storage size unit enumeration
│   │   ├── URLFinder.php
│   │   └── URLFinder/              URL detection in plain text
│   │
│   ├── DateTimeHelper/         DateTime utilities module
│   │   ├── DateTimeHelper.php      Static date/time utilities
│   │   ├── DateIntervalExtended.php  QoL wrapper for DateInterval
│   │   ├── DateTimeException.php
│   │   ├── DaytimeStringInfo.php     Parses/validates "HH:MM" daytime strings
│   │   ├── DurationConverter.php     Duration value conversion
│   │   ├── DurationStringInfo.php    Parses/manipulates "1h 30m 15s" duration strings
│   │   ├── IntervalConverter.php     DateInterval conversion
│   │   ├── TimeConverter.php         Time unit conversion
│   │   └── TimeDurationCalculator.php  Start/end/duration calculator
│   │
│   ├── Exceptions/             Shared exception base classes
│   │   └── BaseException.php       Base exception class for the whole library
│   │
│   ├── FileHelper/             File system utilities module
│   │   ├── FileHelper.php          Static file system utilities (facade over FileInfo/FolderInfo)
│   │   ├── AbstractPathInfo.php    Shared base for FileInfo / FolderInfo
│   │   ├── CLICommandChecker.php   CLI tool availability check
│   │   ├── Exception.php
│   │   ├── FileDownloader.php      HTTP file download helper
│   │   ├── FileFinder.php
│   │   ├── FileFinder/             File search/filter engine
│   │   │   └── FileCollector.php
│   │   ├── FileInfo.php            File information and manipulation object
│   │   ├── FileInfo/               FileInfo sub-classes (LineReader, FileSender, etc.)
│   │   ├── FileInfoInterface.php
│   │   ├── FolderFinder.php        Folder search/filter engine
│   │   ├── FolderInfo.php          Folder information and manipulation object
│   │   ├── FolderInfo/             FolderInfo sub-classes (FileCreator, etc.)
│   │   ├── FolderInfoInterface.php
│   │   ├── FolderTree.php          Whole folder-tree manipulation (copy, delete)
│   │   ├── IndeterminatePath.php   Path that may be a file or folder
│   │   ├── JSONFile.php            Specialized JSON file handler
│   │   ├── JSONFile/               JSON file sub-classes
│   │   ├── MimeTypes.php           MIME type database and helpers
│   │   ├── MimeTypesEnum.php       Enum of known MIME types
│   │   ├── PathInfoInterface.php   Common interface for path info objects
│   │   ├── PathRelativizer.php     Relativize two paths against each other
│   │   ├── PathsReducer.php        Reduce a list of paths to shortest form
│   │   ├── PHPClassInfo.php
│   │   ├── PHPClassInfo/           PHP class metadata parsing (no reflection)
│   │   ├── PHPFile.php             Specialized PHP file handler
│   │   ├── SerializedFile.php      Specialized serialize()-based file handler
│   │   ├── UnicodeHandling.php     Unicode file content helpers
│   │   └── UploadFileSizeInfo.php  PHP upload limits helper
│   │
│   ├── Highlighter/            Syntax highlighter module
│   │   ├── Highlighter.php         highlight.php-backed syntax highlighter (themes, inline styles)
│   │   ├── HighlighterException.php
│   │   └── StyleInliner.php
│   │
│   ├── HTMLHelper/             HTML markup utilities module
│   │   ├── HTMLHelper.php          Static HTML markup utilities
│   │   └── HTMLHelperException.php
│   │
│   ├── HTMLTag/                OO HTML tag builder module
│   │   ├── HTMLTag.php             OO HTML tag builder
│   │   ├── CannedTags.php
│   │   └── GlobalOptions.php
│   │
│   ├── Interfaces/             Pure interfaces (no implementation)
│   │   ├── AttributableInterface.php
│   │   ├── ClassableAttributeInterface.php
│   │   ├── ClassableInterface.php
│   │   ├── OptionableInterface.php
│   │   ├── RenderableInterface.php
│   │   ├── RuntimePropertizableInterface.php
│   │   ├── StringableInterface.php
│   │   └── StylableInterface.php
│   │
│   ├── JSHelper/               JavaScript generation utilities module
│   │   ├── JSHelper.php            JavaScript generation utilities
│   │   ├── JSHelperException.php
│   │   └── QuoteConverter.php
│   │
│   ├── Microtime/              Microsecond-precision DateTime module
│   │   ├── Microtime.php           DateTime subclass with microsecond/nanosecond precision
│   │   ├── DateFormatChars.php
│   │   ├── DateParseResult.php
│   │   ├── Exception.php
│   │   └── TimeZones/              Timezone info sub-classes
│   │       ├── NamedTimeZoneInfo.php
│   │       ├── OffsetParser.php
│   │       └── TimeZoneInfo.php
│   │
│   ├── NamedClosure/           Closure wrapper module
│   │   └── NamedClosure.php        Closure wrapper with a human-readable name
│   │
│   ├── NumberInfo/             Number+unit value object module
│   │   ├── NumberInfo.php          Mutable number+unit value object
│   │   ├── Comparer.php
│   │   └── Immutable.php           Immutable variant (NumberInfo_Immutable)
│   │
│   ├── OutputBuffering/        PHP output buffering wrapper module
│   │   ├── OutputBuffering.php     Static OO wrapper around PHP output buffering
│   │   └── Exception.php
│   │
│   ├── RegexHelper/            Regex utilities module
│   │   └── RegexHelper.php         Regex utility methods and pattern constants
│   │
│   ├── RequestHelper/          HTTP multipart POST module
│   │   ├── RequestHelper.php       HTTP multipart POST request builder/sender via cURL
│   │   ├── Boundaries.php
│   │   ├── Boundaries/
│   │   │   └── Boundary.php
│   │   ├── CURL.php
│   │   ├── Exception.php
│   │   └── Response.php
│   │
│   ├── RGBAColor/              RGBA/HSV color value object module
│   │   ├── RGBAColor.php           RGBA color value object
│   │   ├── HSVColor.php            HSV color value object
│   │   ├── ArrayConverter.php
│   │   ├── ColorChannel.php
│   │   ├── ColorChannel/           ColorChannel value object
│   │   ├── ColorComparator.php
│   │   ├── ColorException.php
│   │   ├── ColorFactory.php        Named constructor entry point for creating RGBAColor
│   │   ├── ColorPresets.php        Named color preset registry
│   │   ├── ColorPresets/
│   │   ├── FormatsConverter.php
│   │   ├── FormatsConverter/       Color format conversion (HEX, CSS, array, HSV)
│   │   ├── PresetsManager.php
│   │   └── UnitsConverter.php
│   │
│   ├── StringBuilder/          Fluent string building module
│   │   ├── StringBuilder.php       Fluent string building with HTML helpers
│   │   ├── Exception.php
│   │   └── Interface.php
│   │
│   ├── StringHelper/           Static string manipulation module
│   │   ├── StringHelper.php        Static string manipulation utilities
│   │   ├── HiddenConverter.php     Invisible character visualization
│   │   ├── QueryParser.php         parse_str() replacement with safe key handling
│   │   ├── StringHelperException.php
│   │   ├── StringMatch.php         String matching helpers
│   │   ├── TabsNormalizer.php      Tab normalization
│   │   ├── TextComparer.php        Text comparison utilities
│   │   ├── WordSplitter.php        Word splitting
│   │   └── WordWrapper.php         Word wrapping
│   │
│   ├── StyleCollection/        CSS inline-style builder module
│   │   ├── StyleCollection.php     OO CSS inline-style builder (fluent)
│   │   ├── StyleBuilder.php
│   │   ├── StyleBuilder/           Style property builder sub-classes (Flavors, containers)
│   │   ├── StyleOptions.php
│   │   └── StylesRenderer.php
│   │
│   ├── ThrowableInfo/          Throwable wrapper module
│   │   ├── ThrowableInfo.php       Extended Throwable wrapper with serialization
│   │   ├── ThrowableCall.php
│   │   ├── ThrowableMessageRenderer.php
│   │   ├── ThrowableSerializer.php
│   │   └── ThrowableStringConverter.php
│   │
│   ├── Traits/                 Concrete trait implementations
│   │   ├── AttributableTrait.php
│   │   ├── ClassableAttributeTrait.php
│   │   ├── ClassableTrait.php
│   │   ├── OptionableTrait.php
│   │   ├── OptionableTrait/
│   │   ├── RenderableBufferedTrait.php
│   │   ├── RenderableTrait.php
│   │   ├── RuntimePropertizableTrait.php
│   │   ├── SimpleErrorStateInterface.php
│   │   ├── SimpleErrorStateTrait.php
│   │   └── StylableTrait.php
│   │
│   ├── Transliteration/        ASCII transliteration module
│   │   └── Transliteration.php     ASCII transliteration for strings
│   │
│   ├── TypeFilter/             Type-safe value filtering
│   │   ├── BaseTypeFilter.php
│   │   ├── LenientType.php     Lenient (coercive) type filter
│   │   └── StrictType.php      Strict type filter
│   │
│   ├── URLInfo/                URL parser module
│   │   ├── URLInfo.php             URL parser and inspector value object
│   │   ├── Parser/             URL tokenizer / parser internals
│   │   ├── URIConnectionTester.php
│   │   ├── URIFilter.php
│   │   ├── URIHighlighter.php
│   │   ├── URINormalizer.php
│   │   ├── URIParser.php
│   │   ├── URISchemes.php
│   │   ├── URLException.php
│   │   ├── URLHosts.php
│   │   └── URLInfoTrait.php
│   │
│   ├── VariableInfo/           Variable introspection module
│   │   ├── VariableInfo.php        PHP variable introspection value object
│   │   ├── VariableRenderer.php
│   │   └── Renderer/               HTML and string renderers for each PHP type
│   │
│   ├── XMLHelper/              XML formatting module
│   │   ├── XMLHelper.php           XML formatting and conversion utilities
│   │   ├── Converter.php
│   │   ├── Converter/
│   │   ├── DOMErrors.php
│   │   ├── DOMErrors/
│   │   ├── Exception.php
│   │   ├── HTMLLoader.php
│   │   ├── LibXML.php
│   │   ├── SimpleXML.php
│   │   └── SimpleXML/
│   │
│   └── _deprecated/            Kept for backwards compatibility; do not use in new code
│       ├── ClassableInterface.php
│       ├── ClassableTrait.php
│       ├── Date.php
│       ├── DateInterval.php
│       ├── DurationConverter.php
│       ├── IntervalConverter.php
│       ├── OptionableInterface.php
│       ├── OptionableTrait.php
│       ├── QueryParser.php
│       ├── String.php
│       ├── Stringable.php
│       ├── StringMatch.php
│       ├── ThrowableInfo.php
│       ├── TimeConverter.php
│       └── WordSplitter.php
│
├── tests/                      Test suite
│   ├── bootstrap.php           PHPUnit bootstrap (autoload + config)
│   ├── config.dist.php         Sample test configuration
│   ├── config.php              Local test configuration (gitignored)
│   ├── tests.php.ini           PHP ini overrides for test runs
│   ├── AppUtilsTestClasses/    Shared test helper classes (autoloaded via classmap)
│   ├── AppUtilsTests/          Actual test cases, mirroring src/ structure
│   ├── assets/                 Test fixture files (JSON, XML, etc.)
│   └── phpstan/                PHPStan config and bootstraps
│
├── docs/                       Documentation and agent manifests
│   └── agents/
│       └── project-manifest/   ← This manifest
│
└── vendor/                     Composer-managed dependencies (do not edit)
    ├── autoload.php
    ├── geshi/
    ├── neitanod/
    ├── nikic/          (used internally by PHPStan/phpunit)
    ├── phpstan/
    ├── phpunit/
    └── …
```
