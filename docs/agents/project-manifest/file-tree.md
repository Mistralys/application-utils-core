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
│   ├── BaseException.php       Base exception class for the whole library
│   ├── ClassHelper.php         Static class reflection and loading utilities
│   ├── ConvertHelper.php       Static string/data conversion utilities (legacy facade)
│   ├── DateTimeHelper.php      Static date/time utilities
│   ├── FileHelper.php          Static file system utilities (facade over FileInfo/FolderInfo)
│   ├── Highlighter.php         GeSHi-backed syntax highlighter
│   ├── HTMLHelper.php          Static HTML markup utilities
│   ├── HTMLTag.php             OO HTML tag builder
│   ├── HSVColor.php            HSV color value object
│   ├── JSHelper.php            JavaScript generation utilities
│   ├── Microtime.php           DateTime subclass with microsecond/nanosecond precision
│   ├── NamedClosure.php        Closure wrapper with a human-readable name
│   ├── NumberInfo.php          Mutable number+unit value object
│   ├── OutputBuffering.php     Static OO wrapper around PHP output buffering
│   ├── RegexHelper.php         Regex utility methods and pattern constants
│   ├── RequestHelper.php       HTTP multipart POST request builder/sender via cURL
│   ├── RGBAColor.php           RGBA color value object
│   ├── StringBuilder.php       Fluent string building with HTML helpers
│   ├── StringHelper.php        Static string manipulation utilities
│   ├── StyleCollection.php     OO CSS inline-style builder (fluent)
│   ├── AttributeCollection.php OO HTML attribute builder (fluent)
│   ├── ThrowableInfo.php       Extended Throwable wrapper with serialization
│   ├── Transliteration.php     ASCII transliteration for strings
│   ├── URLInfo.php             URL parser and inspector value object
│   ├── VariableInfo.php        PHP variable introspection value object
│   ├── XMLHelper.php           XML formatting and conversion utilities
│   │
│   ├── ArrayDataCollection/    Support classes for ArrayDataCollection
│   │   ├── ArrayDataCollectionException.php
│   │   ├── ArrayDataObservable.php   Observable variant (change listeners)
│   │   ├── ArrayFlavors.php          Array filtering/conversion options builder
│   │   └── ArraySetters.php          Helper for modifying array-type keys
│   ├── ArrayDataCollection.php       Type-safe associative array collection
│   │
│   ├── AttributeCollection/    (reserved for future AttributeCollection sub-classes)
│   │
│   ├── ClassHelper/            ClassHelper support classes
│   │   ├── BaseClassHelperException.php
│   │   ├── ClassLoaderNotFoundException.php
│   │   ├── ClassNotExistsException.php
│   │   ├── ClassNotImplementsException.php
│   │   └── Repository/         ClassRepository and ClassRepositoryManager
│   │
│   ├── ConvertHelper/          ConvertHelper sub-utilities
│   │   ├── Array.php           Array conversion helpers
│   │   ├── Bool.php            Boolean conversion helpers
│   │   ├── ByteConverter.php   Byte ↔ human-readable size conversion
│   │   ├── Comparator.php      String comparison
│   │   ├── ControlCharacters.php  Control character utilities
│   │   ├── EOL.php             End-of-line detection and normalization
│   │   ├── Exception.php
│   │   ├── JSONConverter/      JSON encode/decode helpers
│   │   ├── SizeNotation.php    Human-readable size notation parser
│   │   ├── StorageSizeEnum/    Storage size unit enumeration
│   │   └── URLFinder/          URL detection in plain text
│   │
│   ├── DateTimeHelper/         DateTime sub-utilities
│   │   ├── DateIntervalExtended.php  QoL wrapper for DateInterval
│   │   ├── DateTimeException.php
│   │   ├── DaytimeStringInfo.php     Parses/validates "HH:MM" daytime strings
│   │   ├── DurationConverter.php     Duration value conversion
│   │   ├── DurationStringInfo.php    Parses/manipulates "1h 30m 15s" duration strings
│   │   ├── IntervalConverter.php     DateInterval conversion
│   │   ├── TimeConverter.php         Time unit conversion
│   │   └── TimeDurationCalculator.php  Start/end/duration calculator
│   │
│   ├── FileHelper/             File system sub-classes
│   │   ├── AbstractPathInfo.php      Shared base for FileInfo / FolderInfo
│   │   ├── CLICommandChecker.php     CLI tool availability check
│   │   ├── Exception.php
│   │   ├── FileDownloader.php        HTTP file download helper
│   │   ├── FileFinder/               File search/filter engine
│   │   ├── FileFinder.php
│   │   ├── FileInfo/                 FileInfo sub-classes (LineReader, FileSender, etc.)
│   │   ├── FileInfo.php              File information and manipulation object
│   │   ├── FileInfoInterface.php
│   │   ├── FolderFinder.php          Folder search/filter engine
│   │   ├── FolderInfo/               FolderInfo sub-classes (FileCreator, etc.)
│   │   ├── FolderInfo.php            Folder information and manipulation object
│   │   ├── FolderInfoInterface.php
│   │   ├── FolderTree.php            Whole folder-tree manipulation (copy, delete)
│   │   ├── IndeterminatePath.php     Path that may be a file or folder
│   │   ├── JSONFile/                 JSON file sub-classes
│   │   ├── JSONFile.php              Specialized JSON file handler
│   │   ├── MimeTypes.php             MIME type database and helpers
│   │   ├── MimeTypesEnum.php         Enum of known MIME types
│   │   ├── PathInfoInterface.php     Common interface for path info objects
│   │   ├── PathRelativizer.php       Relativize two paths against each other
│   │   ├── PathsReducer.php          Reduce a list of paths to shortest form
│   │   ├── PHPClassInfo/             PHP class metadata parsing (no reflection)
│   │   ├── PHPClassInfo.php
│   │   ├── PHPFile.php               Specialized PHP file handler
│   │   ├── SerializedFile.php        Specialized serialize()-based file handler
│   │   ├── UnicodeHandling.php       Unicode file content helpers
│   │   └── UploadFileSizeInfo.php    PHP upload limits helper
│   │
│   ├── Highlighter/            Highlighter support (language configs, etc.)
│   ├── HTMLHelper/             HTMLHelper support classes
│   ├── HTMLTag/                HTMLTag sub-classes (GlobalOptions, etc.)
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
│   ├── JSHelper/               JSHelper sub-classes
│   ├── Microtime/              Microtime sub-classes (TimeZoneInfo, etc.)
│   ├── NumberInfo/             NumberInfo sub-classes (NumberInfo_Immutable, etc.)
│   ├── OutputBuffering/        OutputBuffering sub-classes
│   ├── RequestHelper/          RequestHelper sub-classes (Response, etc.)
│   ├── RGBAColor/              RGBAColor sub-classes
│   │   ├── ArrayConverter.php
│   │   ├── ColorChannel/       ColorChannel value object
│   │   ├── ColorChannel.php
│   │   ├── ColorComparator.php
│   │   ├── ColorException.php
│   │   ├── ColorFactory.php    Named constructor entry point for creating RGBAColor
│   │   ├── ColorPresets/
│   │   ├── ColorPresets.php    Named color preset registry
│   │   ├── FormatsConverter/   Color format conversion (HEX, CSS, array, HSV)
│   │   ├── FormatsConverter.php
│   │   ├── PresetsManager.php
│   │   └── UnitsConverter.php
│   │
│   ├── StringBuilder/          StringBuilder sub-classes (Interface, Exception)
│   ├── StringHelper/           StringHelper sub-classes
│   │   ├── HiddenConverter.php     Invisible character visualization
│   │   ├── QueryParser.php         parse_str() replacement with safe key handling
│   │   ├── StringHelperException.php
│   │   ├── StringMatch.php         String matching helpers
│   │   ├── TabsNormalizer.php      Tab normalization
│   │   ├── TextComparer.php        Text comparison utilities
│   │   ├── WordSplitter.php        Word splitting
│   │   └── WordWrapper.php         Word wrapping
│   │
│   ├── StyleCollection/        StyleCollection sub-classes
│   ├── ThrowableInfo/          ThrowableInfo sub-classes (ThrowableCall, etc.)
│   ├── Traits/                 Concrete trait implementations
│   │   ├── AttributableTrait.php
│   │   ├── ClassableAttributeTrait.php
│   │   ├── ClassableTrait.php
│   │   ├── OptionableTrait/
│   │   ├── OptionableTrait.php
│   │   ├── RenderableBufferedTrait.php
│   │   ├── RenderableTrait.php
│   │   ├── RuntimePropertizableTrait.php
│   │   ├── SimpleErrorStateInterface.php
│   │   ├── SimpleErrorStateTrait.php
│   │   └── StylableTrait.php
│   │
│   ├── TypeFilter/             Type-safe value filtering
│   │   ├── BaseTypeFilter.php
│   │   ├── LenientType.php     Lenient (coercive) type filter
│   │   └── StrictType.php      Strict type filter
│   │
│   ├── URLInfo/                URLInfo sub-classes
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
│   ├── VariableInfo/           VariableInfo sub-classes
│   ├── XMLHelper/              XMLHelper sub-classes
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
