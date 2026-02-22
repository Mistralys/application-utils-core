# Public API Surface

All classes reside in the `AppUtils` namespace unless otherwise noted.  
Only **public** constructors, static factories, properties, and method signatures are listed. No implementation logic is included.

---

## Global Helper Functions (`src/functions.php`)

```php
namespace AppUtils;

function parseVariable(mixed $variable) : VariableInfo
function parseThrowable(Throwable $e) : ThrowableInfo
function restoreThrowable(array $serialized) : ThrowableInfo
function parseURL(string $url) : URLInfo
function parseNumber(NumberInfo|string|int|float|null $value, bool $forceNew=false) : NumberInfo
function parseNumberImmutable(NumberInfo|string|int|float|null $value) : NumberInfo_Immutable
function parseDurationString(string|int|DurationStringInfo|DateInterval|DateIntervalExtended|null $duration) : DurationStringInfo
function parseDaytimeString(string|int|DaytimeStringInfo|null $time) : DaytimeStringInfo
function parseInterval(DateInterval $interval) : DateIntervalExtended
function sb() : StringBuilder
function attr(array|string|null $attributes=null) : AttributeCollection
function t(string $text, string|int|float|StringableInterface ...$placeholderValues) : string
function array_remove_values(array $haystack, array $values, bool $strict=true) : array
function init() : void
```

---

## Module: Class Utilities

### `ClassHelper` (static utilities)

```php
class ClassHelper
{
    public static function resolveClassName(string $legacyName, string $nsPrefix='') : ?string
    public static function requireResolvedClass(string $legacyName, string $nsPrefix='') : string
    public static function requireClassExists(string $className) : void
    public static function requireClassInstanceOf(string $targetClass, string $expectedClass) : void
    public static function isClassInstanceOf(string $targetClass, string $instanceClass) : bool
    public static function requireObjectInstanceOf(string $class, object $object, int $errorCode=0) : void
    public static function getClassLoader() : ClassLoader
    public static function getClassTypeName(mixed $subject) : string
    public static function getClassNamespace(mixed $subject) : string
    public static function resolveClassByReference(string $classID, string $referenceClass) : string
    public static function getClassesInFolder(FolderInfo $folder, string $classReference) : array
    public static function findClassesInFolder(FolderInfo $folder, bool $recursive=false, ?string $instanceOf=null) : array
    public static function findClassesInRepository(FolderInfo $folder, bool $recursive=false, ?string $instanceOf=null) : ClassRepository
    public static function setCacheFolder(mixed $folder) : void
    public static function getCacheFolder() : ?FolderInfo
    public static function getRepositoryManager() : ClassRepositoryManager
    public static function getClassSourceFile(string|object $class) : ?PHPFile
}
```

### `ClassRepository`

```php
class ClassRepository
{
    public function __construct(string $id, array $classes)
    public function getID() : string
    public function getClasses() : array   // string[] — fully qualified class names
}
```

### `ClassRepositoryManager`

```php
class ClassRepositoryManager
{
    public static function create(string|PathInfoInterface|SplFileInfo $cacheFolder) : ClassRepositoryManager
    public static function createDefault() : ClassRepositoryManager
    public function getCacheFolder() : FolderInfo
    public function getCacheFile() : PHPFile
    public function findClassesInFolder(FolderInfo $folder, bool $recursive=false, ?string $instanceOf=null, ?string $id=null) : ClassRepository
    public function getByID(string $id) : ?ClassRepository
    public function requireByID(string $id) : ClassRepository
    public function registerClassLoader(string $id, Closure $callback) : self
    public function unregisterClassLoader(string $repositoryID) : self
    public function hasClassLoader(string $repositoryID) : bool
    public function clearID(string $id) : self
    public function clearCache() : self
    public function idExists(string $id) : bool
    public function initializeCache(string $id, array $classInfos) : ClassRepository
    public function writeCache() : self
}
```

---

## Module: File System

### `FileHelper` (static facade)

```php
class FileHelper
{
    public static function parseSerializedFile(string|PathInfoInterface|SplFileInfo $file) : array
    public static function deleteTree(string|PathInfoInterface|SplFileInfo $rootFolder) : bool
    public static function createFolder(string|PathInfoInterface|SplFileInfo $path) : FolderInfo
    public static function getFolderInfo(string|PathInfoInterface|SplFileInfo $path) : FolderInfo
    public static function copyTree(string|PathInfoInterface|SplFileInfo $source, string|PathInfoInterface|SplFileInfo $target) : void
    public static function copyFile(string|PathInfoInterface|SplFileInfo $sourcePath, string|PathInfoInterface|SplFileInfo $targetPath) : void
    public static function deleteFile(string|PathInfoInterface|SplFileInfo $filePath) : void
    public static function getFileInfo(string|PathInfoInterface|SplFileInfo $path) : FileInfo
    public static function getPathInfo(string|PathInfoInterface|SplFileInfo $path) : PathInfoInterface
    public static function detectMimeType(string|PathInfoInterface|SplFileInfo $fileName) : ?string
    public static function sendFileAuto(string|PathInfoInterface|SplFileInfo $filePath, string $fileName='') : void
    public static function sendFile(string|PathInfoInterface|SplFileInfo $filePath, ?string $fileName=null, bool $asAttachment=true) : void
    public static function downloadFile(string $url, int $timeout=0, bool $SSLEnabled=false) : string
    public static function isPHPFile(string|PathInfoInterface|SplFileInfo $filePath) : bool
    public static function getExtension(string|PathInfoInterface|SplFileInfo $fileName, bool $lowercase=true) : string
    public static function getFilename(string|PathInfoInterface|SplFileInfo $pathOrDirIterator, bool $extension=true) : string
    public static function parseJSONFile(string|PathInfoInterface|SplFileInfo $file, string $targetEncoding='', string|array|null $sourceEncoding=null) : array
    public static function fixFileName(string $name) : string
    public static function createFileFinder(string|PathInfoInterface|SplFileInfo $path) : FileFinder
    public static function findHTMLFiles(string|PathInfoInterface|SplFileInfo $targetFolder, array $options=array()) : array
    public static function findPHPFiles(string|PathInfoInterface|SplFileInfo $targetFolder, array $options=array()) : array
    public static function detectWindowsDriveLetter(string $path) : ?string
    public static function removeWindowsDriveLetter(string $path) : string
    public static function resolvePathDots(string $path) : string
    /** @deprecated Use FolderInfo::addSubFile() or FolderInfo::saveJSONFile() instead */
    public static function saveJSON(mixed $data, string|PathInfoInterface|SplFileInfo $file, bool $pretty=false) : void
}
```

### `FileInfo`

```php
class FileInfo extends AbstractPathInfo implements FileInfoInterface
{
    public static function factory(mixed $path) : FileInfo
    public static function clearCache() : void
    public static function is_file(string $path) : bool
    public function removeExtension(bool $keepPath=false) : string
    public function getBaseName() : string
    public function getSize() : int
    public function getExtension(bool $lowercase=true) : string
    public function getFolder() : FolderInfo
    public function getFolderPath() : string
    public function delete() : FileInfo
    public function copyTo(mixed $targetPath) : FileInfo
    public function getLineReader() : LineReader
    public function getContents() : string
    public function putContents(string $content) : self
    public function getDownloader() : FileSender
    public function detectEOLCharacter() : ?ConvertHelper_EOL
    public function countLines() : int
    public function getLine(int $lineNumber) : ?string
    public function getMimeType() : string
    public function send(string $fileName, ?bool $asAttachment=false) : self
    public function getTypeLabel() : string
}
```

### `FolderInfo`

```php
class FolderInfo extends AbstractPathInfo implements FolderInfoInterface
{
    public static function factory(string|PathInfoInterface|SplFileInfo $path) : FolderInfo
    public static function clearCache() : void
    public static function is_dir(string $path) : bool
    public function getTypeLabel() : string
    public function delete() : FolderInfo
    public function create() : self
    public function getRelativeTo(FolderInfo $folder) : string
    public function createFolderFinder() : FolderFinder
    public function getIterator() : DirectoryIterator
    public function getExtension(bool $lowercase=true) : string
    public function getSize(bool $recursive=true) : int
    public function getFolderPath() : string
    public function createSubFolder(string $name) : FolderInfo
    public function addSubFile() : FileCreator
    public function saveFile(string $fileName, string $content='') : FileInfo
    public function saveJSONFile(array $data, string $fileName, bool $pretty=false) : JSONFile
    public function getSubFolders(bool $recursive=false) : array
    public function getSubFile(string $nameOrRelativePath) : FileInfo
    public function isEmpty() : bool
    public function createFileFinder() : FileFinder
    public function getSubFiles() : array
    public function getParentFolder() : ?FolderInfo
    public function findPHPClasses(bool $recursive=false) : array
}
```

### `JSONFile`

```php
class JSONFile extends FileInfo
{
    public function getData() : array
    public function putData(array $data, bool $pretty=false) : self
}
```

### `PHPFile`

```php
class PHPFile extends FileInfo
{
    public function putStatements(array $statements) : self
}
```

### `SerializedFile`

```php
class SerializedFile extends FileInfo
{
    public function getData() : array
    public function putData(array $data) : self
}
```

### `FileFinder`

```php
class FileFinder
{
    // Fluent filter/search API — call getAll() to retrieve results
    public function includeExtensions(array $extensions) : self
    public function excludeExtensions(array $extensions) : self
    public function setRecursive(bool $recursive=true) : self
    public function getAll() : array        // returns FileInfo[]
    public function getMatchedFiles() : array
}
```

### `FolderFinder`

```php
class FolderFinder
{
    public function setRecursive(bool $recursive=true) : self
    public function getAll() : array        // returns FolderInfo[]
}
```

### `PathsReducer`

```php
class PathsReducer
{
    public function __construct(array $paths)
    public function reduce() : array
}
```

### `PathRelativizer`

```php
class PathRelativizer
{
    public static function relativize(string $absolutePath, string $relativeTo) : string
}
```

### `MimeTypes`

```php
class MimeTypes
{
    public static function getMimeTypesByExtension(string $extension) : array
    public static function getExtensionsByMimeType(string $mimeType) : array
    public static function mimeTypeExists(string $mimeType) : bool
    public static function extensionExists(string $extension) : bool
}
```

### `PHPClassInfo`

```php
class PHPClassInfo
{
    public function __construct(string $filePath)
    public function getClasses() : array
    public function hasClasses() : bool
    public function getNamespace() : string
}
```

---

## Module: Data Structures

### `ArrayDataCollection`

```php
class ArrayDataCollection
{
    public function __construct(array $data=array())
    public static function create(ArrayDataCollection|array|null $data=array()) : self
    public static function createFromJSON(string $json) : ArrayDataCollection
    public function getData() : array
    public function setKeys(array $data) : self
    public function setKey(string $name, mixed $value) : self
    public function setArray(string $name) : ArraySetters
    public function mergeWith(ArrayDataCollection $collection) : self
    public function combine(ArrayDataCollection $collection) : ArrayDataCollection
    public function getKey(string $name) : mixed
    public function getString(string $name) : string
    public function getStringN(string $name) : ?string
    public function getInt(string $name) : int
    public function getFloat(string $name) : float
    public function getBool(string $name) : bool
    public function getArray(string $name) : array
    public function getArrayFlavored(string $name) : ArrayFlavors
    public function getJSONArray(string $name) : array
    public function getMicrotime(string $name) : ?Microtime
    public function getDateTime(string $name) : ?DateTime
    public function requireMicrotime(string $name) : Microtime
    public function requireDateTime(string $name) : DateTime
    public function keyExists(string $name) : bool
    public function keyHasValue(string $name) : bool
    public function removeKey(string $name) : self
    public function getKeys() : array
    public function clearKeys() : self
}
```

### `ArrayDataObservable` (extends `ArrayDataCollection`)

```php
class ArrayDataObservable extends ArrayDataCollection
{
    public function onKeyChanged(callable $callback) : self
    public function onAnyKeyChanged(callable $callback) : self
}
```

### `NumberInfo`

```php
class NumberInfo
{
    public function __construct(mixed $value)
    public function setValue(mixed $value) : self
    public function setNumber(mixed $number) : self
    public function getValue() : mixed
    public function getNumber() : mixed
    public function getUnits() : string
    public function getRawInfo() : array
    public function getInstanceID() : int
    public function isEmpty() : bool
    public function hasValue() : bool
    public function isPositive() : bool
    public function isNegative() : bool
    public function isZero() : bool
    public function isZeroOrEmpty() : bool
    public function hasUnits() : bool
    public function isPixels() : bool
    public function isPercent() : bool
    public function isEM() : bool
    public function hasDecimals() : bool
    public function isEven() : bool
    public function toAttribute() : string
}
```

### `NumberInfo_Immutable` (extends `NumberInfo`)

All mutating operations return a new `NumberInfo_Immutable` instance rather than modifying in place.

### `URLInfo`

```php
class URLInfo
{
    public function __construct(string $url)
    public static function filterURL(string $url) : string
    public function getParser() : URIParser
    public function setUTFEncoding(bool $enabled=true) : URLInfo
    public function isUTFEncodingEnabled() : bool
    public function isSecure() : bool
    public function isAnchor() : bool
    public function isEmail() : bool
    public function isPhoneNumber() : bool
    public function isURL() : bool
    public function isValid() : bool
    public function getHost() : string
    public function getPath() : string
    public function getFragment() : string
    public function getScheme() : string
    public function getPort() : int
    public function getQuery() : string
    public function getUsername() : string
    public function getPassword() : string
    public function hasPort() : bool
    public function hasQuery() : bool
    public function hasFragment() : bool
    public function hasPath() : bool
    public function getParams() : array
    public function getParam(string $name) : string
    public function hasParam(string $name) : bool
    public function setParam(string $name, string $value) : URLInfo
    public function removeParam(string $name) : URLInfo
    public function getNormalized() : string
    public function getHighlighted() : string
    public function __toString() : string
}
```

### `ThrowableInfo`

```php
class ThrowableInfo
{
    public static function fromThrowable(Throwable $e) : ThrowableInfo
    public static function fromSerialized(array $serialized) : ThrowableInfo
    public function getCode() : int
    public function getMessage() : string
    public function getDefaultOptions() : array
    public function hasPrevious() : bool
    public function getPrevious() : ThrowableInfo
    public function hasCode() : bool
    public function toString() : string
    public function getReferer() : string
    public function isCommandLine() : bool
    public function isWebRequest() : bool
    public function getContext() : string
    public function getDate() : Microtime
    public function serialize() : array
    public function setFolderDepth(int $depth) : ThrowableInfo
    public function getFolderDepth() : int
    public function getCalls() : array   // ThrowableCall[]
    public function getFinalCall() : ThrowableCall
    public function countCalls() : int
    public function getClass() : string
}
```

### `VariableInfo`

```php
class VariableInfo
{
    public function __construct(mixed $value, ?array $serialized=null)
    public static function fromVariable(mixed $variable) : VariableInfo
    public static function fromSerialized(array $serialized) : VariableInfo
    public static function callback2string(mixed $callback) : string
    public function getValue() : mixed
    public function getType() : string
    public function enableType(bool $enable=true) : VariableInfo
    public function toString() : string
    public function toHTML() : string
    public function isInteger() : bool
    public function isString() : bool
    public function isCallable() : bool
    public function isBoolean() : bool
    public function isDouble() : bool
    public function isArray() : bool
    public function isNull() : bool
    public function isResource() : bool
    public function isObject() : bool
    public function isType(string $type) : bool
    public function __toString() : string
}
```

---

## Module: Strings

### `StringHelper` (static utilities)

```php
class StringHelper
{
    public static function findString(string $needle, string $haystack, bool $caseInsensitive=false) : array
    public static function toArray(string $string) : array
    public static function toBytes(string $string) : int
    public static function toHash(string $string) : string
    public static function toShortHash(string $string) : string
    public static function toUtf8(string $string) : string
    public static function isASCII(mixed $string) : bool
    public static function isHTML(string $string) : bool
    public static function isUnicode(string $string) : bool
    public static function isCharUppercase(string $char) : bool
    public static function normalizeTabs(string $string, bool $tabs2spaces=false) : string
    public static function tabs2spaces(string $string, int $tabSize=4) : string
    public static function spaces2tabs(string $string, int $tabSize=4) : string
    public static function hidden2visible(string $string) : string
    public static function wordwrap(string $str, int $width=75, string $break="\n", bool $cut=false) : string
    public static function transliterate(string $string, string $spaceChar='-', bool $lowercase=true) : string
    public static function cutText(string $text, int $targetLength, string $append='...') : string
    public static function explodeTrim(string $delimiter, string $string) : array
    public static function camel2snake(string $camelCase, bool $transliterate=false) : string
    public static function snake2camel(string $snakeCase, bool $transliterate=false) : string
    public static function explodeWords(string $subject, ?array $wordChars=null) : ?array
}
```

### `ConvertHelper` (static utilities — legacy facade, still fully usable)

```php
class ConvertHelper
{
    public static function normalizeTabs(string $string, bool $tabs2spaces=false) : string
    public static function tabs2spaces(string $string, int $tabSize=4) : string
    public static function spaces2tabs(string $string, int $tabSize=4) : string
    public static function hidden2visible(string $string) : string
    public static function time2string(float|int|string $seconds) : string
    public static function duration2string(mixed $datefrom, mixed $dateto=-1) : string
    public static function highlight_sql(string $sql) : string
    public static function highlight_xml(string $xml, bool $formatSource=false) : string
    public static function highlight_php(string $phpCode) : string
    public static function bytes2readable(int $bytes, int $precision=1, int $base=ConvertHelper_StorageSizeEnum::BASE_10) : string
    public static function parseBytes(int $bytes) : ConvertHelper_ByteConverter
    public static function text_cut(string $text, int $targetLength, string $append='...') : string
    public static function var_dump(mixed $var, bool $html=true) : string
    public static function print_r(mixed $var, bool $return=false, bool $html=true) : string
    public static function string2bool(mixed $string) : bool
    public static function isBooleanString(mixed $string) : bool
    public static function text_makeXMLCompliant(string $text) : string
    public static function date2listLabel(DateTime $date, bool $includeTime=false, bool $shortMonth=false) : string
    public static function month2string(mixed $monthNr, bool $short=false) : string
    public static function transliterate(string $string, string $spaceChar='-', bool $lowercase=true) : string
    public static function getControlCharactersAsHex() : array
    public static function toString(mixed $value) : string
    public static function toStringN(mixed $value) : ?string
}
```

### `StringBuilder`

```php
class StringBuilder implements StringableInterface
{
    public function __construct()
    public function setSeparator(string $separator) : StringBuilder
    public function add(mixed $string) : StringBuilder
    public function nospace(mixed $string) : StringBuilder
    public function html(mixed $html) : StringBuilder
    public function ul(array $items, ?AttributeCollection $attributes=null) : StringBuilder
    public function ol(array $items, ?AttributeCollection $attributes=null) : StringBuilder
    public function t(string $format, mixed ...$arguments) : StringBuilder
    public function tex(string $format, string $context, mixed ...$arguments) : StringBuilder
    public function age(DateTime $since) : StringBuilder
    public function quote(mixed $string) : StringBuilder
    public function reference(mixed $string) : StringBuilder
    public function sf(string $format, mixed ...$arguments) : StringBuilder
    public function bold(mixed $string, ?AttributeCollection $attributes=null) : StringBuilder
    public function nl() : StringBuilder
    public function eol() : StringBuilder
    public function time() : StringBuilder
    public function note() : StringBuilder
    public function noteBold() : StringBuilder
    public function hint() : StringBuilder
    public function hintBold() : StringBuilder
    public function para(mixed $content, ?AttributeCollection $attributes=null) : StringBuilder
    public function spanned(mixed $content, ?AttributeCollection $attributes=null) : StringBuilder
    public function link(string $label, string $url) : StringBuilder
    public function __toString() : string
}
```

### `OutputBuffering` (static)

```php
class OutputBuffering
{
    public static function isActive() : bool
    public static function getLevel() : int
    public static function start() : void
    public static function stop() : void
    public static function flush() : void
    public static function get() : string
}
```

---

## Module: HTML Markup

### `HTMLHelper` (static)

```php
class HTMLHelper
{
    public static function stripComments(string $html) : string
    public static function injectAtEnd(string $text, string $html) : string
    public static function formatHTML(string $html) : string
}
```

### `HTMLTag`

```php
class HTMLTag implements RenderableInterface
{
    public function __construct(string $name, AttributeCollection $attributes)
    public static function create(string $name, ?AttributeCollection $attributes=null) : HTMLTag
    public static function getGlobalOptions() : GlobalOptions
    public function getName() : string
    public function setSelfClosing(bool $selfClosing=true) : self
    public function isSelfClosing() : bool
    public function setEmptyAllowed(bool $allowed=true) : self
    public function isEmptyAllowed() : bool
    public function hasAttributes() : bool
    public function isEmpty() : bool
    public function render() : string
    public function renderOpen() : string
    public function renderClose() : string
    public function renderContent() : string
    public function getSelfClosingChar() : string
    public function addText(string|int|float|StringableInterface|null $content) : self
    public function addHTML(string|int|float|StringableInterface|null $content) : self
    public function setContent(string|int|float|StringableInterface|null $content) : self
    public function appendContent(string|int|float|StringableInterface|null $content) : self
    public function attr(string $name, string|int|float|bool|StringableInterface|null $value, bool $keepIfEmpty=false) : self
    public function __toString() : string
}
```

### `AttributeCollection`

```php
class AttributeCollection implements RenderableInterface
{
    public static function create(array $attributes=array()) : AttributeCollection
    public static function createAuto(mixed $attributes=null) : AttributeCollection
    public function setAttributes(array $attributes) : self
    public function setAttributeString(string $attributes) : self
    public function getAttribute(string $name, string $default='') : string
    public function attr(string $name, string|int|float|bool|StringableInterface|null $value) : AttributeCollection
    public function attrQuotes(string $name, mixed $value) : AttributeCollection
    public function attrURL(string $name, string $url) : AttributeCollection
    public function prop(string $name, bool $enabled=true) : AttributeCollection
    public function name(?string $name) : AttributeCollection
    public function id(?string $id) : AttributeCollection
    public function remove(string $name) : AttributeCollection
    public function hasAttribute(string $name) : bool
    public function hasAttributes() : bool
    public function getAttributes() : array
    public function getRawAttributes() : array
    public function setKeepIfEmpty(string $name, bool $keep=true) : self
    public function isKeepIfEmpty(string $name) : bool
    public function getStyles() : StyleCollection
    public function render() : string
    public function __toString() : string
}
```

### `StyleCollection`

```php
class StyleCollection implements RenderableInterface
{
    public function __construct(array $styles=array())
    public static function create(array $styles=array()) : StyleCollection
    public static function merge(StyleCollection ...$collections) : StyleCollection
    public function hasStyles() : bool
    public function getStyles() : array
    public function getStyle(string $name) : ?string
    public function parseStylesString(string $string) : StyleCollection
    public function setStyles(array $styles) : StyleCollection
    public function style(string $name, ?string $value, bool $important=false) : StyleCollection
    public function styleAuto(string $name, mixed $value, bool $important=false) : StyleCollection
    public function stylePX(string $name, int $px, bool $important=false) : StyleCollection
    public function stylePercent(string $name, float $percent, bool $important=false) : StyleCollection
    public function styleEM(string $name, float $em, bool $important=false) : StyleCollection
    public function styleREM(string $name, float $em, bool $important=false) : StyleCollection
    public function styleParseNumber(string $name, mixed $value, bool $important=false) : StyleCollection
    public function styleNumber(string $name, NumberInfo $info, bool $important=false) : StyleCollection
    public function remove(string $name) : StyleCollection
    public function mergeWith(StyleCollection $collection) : StyleCollection
    public function display() : StyleCollection
    public function render() : string
    public function __toString() : string
}
```

---

## Module: Date & Time

### `DateTimeHelper` (static)

```php
class DateTimeHelper
{
    public static function time2string(float|int|string $seconds) : string
    public static function duration2string(mixed $datefrom, mixed $dateto=-1) : string
    public static function durationString2interval(string $durationString) : DateIntervalExtended
    public static function toDayName(DateTime $date, bool $short=false) : ?string
    public static function getDayNamesInvariant() : array
    public static function getDayNames(bool $short=false) : array
    public static function toListLabel(DateTime $date, bool $includeTime=false, bool $shortMonth=false) : string
    public static function month2string(mixed $monthNr, bool $short=false) : string
    public static function toTimestamp(DateTime $date) : int
    public static function fromTimestamp(int $timestamp) : DateTime
    public static function interval2string(DateInterval $interval) : string
    public static function interval2days(DateInterval $interval) : int
    public static function interval2hours(DateInterval $interval) : int
    public static function interval2minutes(DateInterval $interval) : int
    public static function interval2seconds(DateInterval $interval) : int
    public static function interval2total(DateInterval $interval, string $unit=DateIntervalExtended::INTERVAL_SECONDS) : int
}
```

### `Microtime` (extends `DateTime`)

```php
class Microtime extends DateTime
{
    public const DATETIME_NOW = 'now';

    public function __construct(mixed $datetime=self::DATETIME_NOW, ?DateTimeZone $timeZone=null)
    public static function createNow(?DateTimeZone $timeZone=null) : Microtime
    public static function createFromString(string $date, ?DateTimeZone $timeZone=null) : Microtime
    public static function createFromMicrotime(Microtime $date) : Microtime
    public static function createFromDate(DateTime $date) : Microtime
    public function getMicroseconds() : int
    public function getMilliseconds() : int
    public function getNanoseconds() : int
    public function getISODate(bool $includeTimeZone=false) : string
    public function getNanoDate(bool $includeTimeZone=false) : string
    public function getMySQLDate() : string
    public function getYear() : int
    public function getMonth() : int
    public function getDay() : int
    public function getHour24() : int
    public function getHour12() : int
    public function isAM() : bool
    public function isPM() : bool
    public function getMeridiem() : string
    public function isToday() : bool
    public function getTimezoneInfo() : TimeZoneInfo
    public function __toString() : string
}
```

### `DateIntervalExtended`

```php
class DateIntervalExtended
{
    public const INTERVAL_SECONDS = 'seconds';
    public const INTERVAL_MINUTES = 'minutes';
    public const INTERVAL_HOURS   = 'hours';
    public const INTERVAL_DAYS    = 'days';

    public static function fromInterval(DateInterval $interval) : DateIntervalExtended
    public function toSeconds() : int
    public function toMinutes() : int
    public function toHours() : int
    public function toDays() : int
    public function getTotal(string $unit) : int
    public function getInterval() : DateInterval
}
```

### `DurationStringInfo`

```php
class DurationStringInfo
{
    public static function fromAuto(mixed $duration) : DurationStringInfo
    public function getHours() : int
    public function getMinutes() : int
    public function getSeconds() : int
    public function getDays() : int
    public function getHoursDec() : float
    public function getMinutesDec() : float
    public function getDaysDec() : float
    public function toInterval() : DateIntervalExtended
    public function toString() : string
    public function isEmpty() : bool
}
```

### `DaytimeStringInfo`

```php
class DaytimeStringInfo
{
    public const ALLOWED_SEPARATOR_CHARS = /* string of allowed separators */;

    public static function fromAuto(mixed $time) : DaytimeStringInfo
    public function getHours() : int
    public function getMinutes() : int
    public function toSeconds() : int
    public function toString() : string
    public function isValid() : bool
    public function isEmpty() : bool
}
```

---

## Module: Colors

### `RGBAColor`

```php
class RGBAColor
{
    public function __construct(ColorChannel $red, ColorChannel $green, ColorChannel $blue, ?ColorChannel $alpha=null, string $name='')
    public function getName() : string
    public function getLabel() : string
    public function getLuma() : int
    public function getLumaPercent() : float
    public function getBrightness() : BrightnessChannel
    public function hasTransparency() : bool
    public function getRed() : ColorChannel
    public function getGreen() : ColorChannel
    public function getBlue() : ColorChannel
    public function getAlpha() : ColorChannel
    public function getTransparency() : ColorChannel
    public function getColor(string $name) : ColorChannel
    public function adjustBrightness(float $percent) : RGBAColor
    public function setRed(ColorChannel $red) : RGBAColor
    public function setGreen(ColorChannel $green) : RGBAColor
    public function setBlue(ColorChannel $blue) : RGBAColor
    public function toHEX() : string
    public function toCSS() : string
    public function toHSV() : HSVColor
    public function toArray() : ArrayConverter
}
```

### `HSVColor`

```php
class HSVColor
{
    // Hue, Saturation, Value color model
    public function getHue() : float
    public function getSaturation() : float
    public function getValue() : float
    public function toRGBA() : RGBAColor
    public function toCSS() : string
    public function toHEX() : string
}
```

### `ColorFactory`

```php
class ColorFactory
{
    public static function createFromHEX(string $hex) : RGBAColor
    public static function createFromRGB(int $r, int $g, int $b) : RGBAColor
    public static function createFromRGBA(int $r, int $g, int $b, float $alpha) : RGBAColor
    public static function createFromCSS(string $css) : RGBAColor
    public static function createFromArray(array $color) : RGBAColor
    public static function createPreset(string $name) : RGBAColor
}
```

---

## Module: HTTP / Network

### `RequestHelper`

```php
class RequestHelper
{
    public function __construct(string $destinationURL)
    public static function createCURL() : CurlHandle
    public static function getBearerToken() : ?string
    public static function clearCache() : void
    public function getMimeBoundary() : string
    public function getMimeBody() : string
    public function getEOL() : string
    public function setTimeout(int $seconds) : RequestHelper
    public function getTimeout() : int
    public function enableLogging(string $targetFile) : RequestHelper
    public function addFile(string $varName, string $fileName, string $content, string $contentType='', string $encoding='') : RequestHelper
    public function addContent(string $varName, string $content, string $contentType) : RequestHelper
    public function addVariable(string $name, string $value) : RequestHelper
    public function setHeader(string $name, string $value) : RequestHelper
    public function disableSSLChecks() : RequestHelper
    public function send() : string
    public function getBody() : string
    public function getResponseHeader() : array
    public function getResponse() : ?RequestHelper_Response
    public function getHeaders() : array
    public function getHeader(string $name) : string
}
```

---

## Module: Exceptions

### `BaseException` (extends `\RuntimeException`)

```php
class BaseException extends \RuntimeException
{
    public function __construct(string $message, ?string $details=null, ?int $code=null, ?Throwable $previous=null)
    public function getDetails() : string
    public function display() : void
    public function getInfo() : ThrowableInfo
    public static function dumpTraceAsString() : void
    public static function dumpTraceAsHTML() : void
    public static function createInfo(Throwable $e) : ThrowableInfo
}
```

---

## Module: Type Filtering

### `StrictType`

```php
class StrictType
{
    public static function int(mixed $value) : int
    public static function string(mixed $value) : string
    public static function float(mixed $value) : float
    public static function bool(mixed $value) : bool
    public static function array(mixed $value) : array
    public static function object(string $class, mixed $value) : object
}
```

### `LenientType`

```php
class LenientType
{
    public static function int(mixed $value, int $default=0) : int
    public static function string(mixed $value, string $default='') : string
    public static function float(mixed $value, float $default=0.0) : float
    public static function bool(mixed $value, bool $default=false) : bool
    public static function array(mixed $value, array $default=array()) : array
}
```

---

## Module: Interfaces (`AppUtils\Interfaces`)

```php
interface StringableInterface extends Stringable
{
    public function __toString() : string;
}

interface RenderableInterface extends StringableInterface
{
    public function render() : string;
    public function display() : void;
}

interface OptionableInterface
{
    public function setOption(string $name, mixed $value) : self;
    public function setOptions(array $options) : self;
    public function getOption(string $name, mixed $default=null) : mixed;
    public function hasOption(string $name) : bool;
    public function getOptions() : array;
    public function getDefaultOptions() : array;           // abstract — must implement
}

interface AttributableInterface
{
    public function attr(string $name, mixed $value) : self;
    public function getAttributes() : AttributeCollection;
}

interface ClassableInterface
{
    public function addClass(string|array $class) : self;
    public function removeClass(string $class) : self;
    public function hasClass(string $class) : bool;
    public function getClasses() : array;
    public function classesToString() : string;
}

interface StylableInterface
{
    public function style(string $name, ?string $value) : self;
    public function getStyles() : StyleCollection;
}

interface RenderableInterface extends StringableInterface
{
    public function render() : string;
    public function display() : void;
}
```

---

## Module: Traits (`AppUtils\Traits`)

### `OptionableTrait`

```php
trait OptionableTrait  // implements OptionableInterface
{
    public function setOption(string $name, mixed $value) : self
    public function setOptions(array $options) : self
    public function getOption(string $name, mixed $default=null) : mixed
    public function getStringOption(string $name, string $default='') : string
    public function getStringOptionNE(string $name, string $default='') : string
    public function getBoolOption(string $name, bool $default=false) : bool
    public function getIntOption(string $name, int $default=0) : int
    public function getArrayOption(string $name) : array
    public function getArrayAdvanced() : ArrayAdvancedOption
    public function hasOption(string $name) : bool
    public function getOptions() : array
    public function isOption(string $name, mixed $value) : bool
    public function setOptionDefault(string $name, mixed $value) : self
    public function getOptionDefault(string $name) : mixed
}
```

### `RenderableTrait`

```php
trait RenderableTrait  // implements RenderableInterface
{
    public function display() : void   // echoes render()
    public function __toString() : string
    // abstract: public function render() : string
}
```

### `AttributableTrait`

```php
trait AttributableTrait  // implements AttributableInterface
{
    public function attr(string $name, mixed $value) : self
    public function getAttributes() : AttributeCollection
}
```

### `ClassableTrait`

```php
trait ClassableTrait  // implements ClassableInterface
{
    public function addClass(string|array $class) : self
    public function removeClass(string $class) : self
    public function hasClass(string $class) : bool
    public function getClasses() : array
    public function classesToString() : string
}
```

### `StylableTrait`

```php
trait StylableTrait  // implements StylableInterface
{
    public function style(string $name, ?string $value) : self
    public function getStyles() : StyleCollection
}
```

### `SimpleErrorStateTrait`

```php
trait SimpleErrorStateTrait
{
    public function hasError() : bool
    public function getError() : string
    public function getErrorCode() : int
}
```

---

## Module: Highlighter / Syntax

### `Highlighter`

```php
class Highlighter
{
    public static function sql(string $sql) : string
    public static function xml(string $xml, bool $formatSource=false) : string
    public static function php(string $code) : string
    public static function json(string $json) : string
}
```

---

## Module: XML

### `XMLHelper`

```php
class XMLHelper
{
    public static function string2xml(string $string) : SimpleXMLElement
    public static function xml2string(SimpleXMLElement $xml, bool $formatOutput=false) : string
    public static function convertArray2xml(array $data, SimpleXMLElement $xml) : void
    public static function stripControlCharacters(string $text) : string
}
```
