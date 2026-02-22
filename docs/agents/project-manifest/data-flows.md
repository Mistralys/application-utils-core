# Key Data Flows

This document traces the main interaction paths through the library. These are not framework request cycles — `application-utils-core` is a standalone utility library. The "flows" here are usage patterns a calling application follows.

---

## 1. Parsing and Inspecting a URL

```
Caller: $info = parseURL('https://example.com/path?foo=bar')
  → new URLInfo($url)
    → URIParser::parse()          // tokenizes scheme, host, path, query, etc.
    → URINormalizer::normalize()  // normalizes the parsed components
  ← URLInfo instance

Caller: $info->getHost()          // 'example.com'
Caller: $info->hasParam('foo')    // true
Caller: $info->setParam('x','1')  // returns new URLInfo
Caller: $info->getHighlighted()
  → URIHighlighter::highlight()   // returns HTML with CSS classes
  ← string (HTML)
```

---

## 2. File Reading and Writing

**Reading a file:**
```
Caller: FileHelper::getFileInfo('/path/to/file.json')
  → FileInfo::factory($path)       // resolves and caches FileInfo instance
  ← FileInfo

FileInfo::getContents()            // reads raw string via file_get_contents
FileInfo::getLineReader()
  → LineReader instance            // lazy line-by-line access
```

**Writing JSON to a folder:**
```
Caller: FolderInfo::factory('/some/dir')
  → Resolves path, creates FolderInfo instance
  ← FolderInfo

FolderInfo::saveJSONFile($data, 'output.json', pretty: true)
  → Creates JSONFile instance
  → json_encode($data, flags)
  → file_put_contents(path, $json)
  ← JSONFile
```

**Searching for files:**
```
Caller: FileHelper::createFileFinder('/project/src')
  → new FileFinder($path)
  ← FileFinder (fluent builder)

FileFinder::includeExtensions(['php'])
           ->setRecursive(true)
           ->getAll()
  → DirectoryIterator traversal with filter pipeline
  ← FileInfo[]
```

---

## 3. Building HTML Markup

**Low-level tag:**
```
Caller: HTMLTag::create('a', attr(['href' => 'https://example.com']))
  → new HTMLTag('a', AttributeCollection)
  ← HTMLTag (fluent)

HTMLTag::addText('Click me')
       ->render()
  → "<a href=\"https://example.com\">Click me</a>"
```

**Attribute collection:**
```
Caller: attr(['class' => 'btn', 'disabled' => true])
  → AttributeCollection::createAuto($attributes)
    → Detects array, calls setAttributes()
  ← AttributeCollection

AttributeCollection::render()  → 'class="btn" disabled'
```

**Style collection:**
```
Caller: StyleCollection::create()
  ← StyleCollection

->stylePX('width', 100)
->stylePercent('height', 50)
->render()
  → 'width:100px;height:50%;'
```

**StringBuilder:**
```
Caller: sb()                   // returns new StringBuilder
  ->bold('Hello')
  ->add(', ')
  ->t('World %s', '!')
  ->nl()
  ->__toString()
  → "<strong>Hello</strong>, World !<br>"
```

---

## 4. Working with Throwables

**Capturing an exception:**
```
try {
    ...
} catch (Throwable $e) {
    $info = parseThrowable($e);
    // or: ThrowableInfo::fromThrowable($e)
}

$info->getMessage()        // exception message
$info->getFinalCall()      // ThrowableCall (file, line, class, method)
$info->serialize()         // array — can be stored in DB or session
```

**Restoring a serialized throwable:**
```
Caller: restoreThrowable($serializedArray)
  → ThrowableInfo::fromSerialized($array)
  ← ThrowableInfo (re-hydrated, no live Throwable needed)
```

---

## 5. Color Manipulation

**Creating and converting a color:**
```
Caller: ColorFactory::createFromHEX('#ff5500')
  → Parses hex → ColorChannel instances for R, G, B
  → new RGBAColor($r, $g, $b)
  ← RGBAColor

RGBAColor::adjustBrightness(20.0)   // returns new RGBAColor
RGBAColor::toHSV()                  // → HSVColor
HSVColor::toRGBA()                  // → RGBAColor  (round-trip)
RGBAColor::toCSS()                  // → 'rgba(255,85,0,1)'
```

---

## 6. Date and Duration Parsing

**Duration string:**
```
Caller: parseDurationString('1h 30m 15s')
  → DurationStringInfo::fromAuto($duration)
    → Parses tokens via regex
  ← DurationStringInfo

->getHours()       // 1
->getMinutes()     // 30
->toInterval()     // → DateIntervalExtended
```

**Daytime string:**
```
Caller: parseDaytimeString('14:30')
  → DaytimeStringInfo::fromAuto($time)
  ← DaytimeStringInfo

->getHours()       // 14
->getMinutes()     // 30
->toSeconds()      // 52200
```

**Date interval conversion:**
```
Caller: DateTimeHelper::interval2total($interval, DateIntervalExtended::INTERVAL_MINUTES)
  → builds DateIntervalExtended internally
  → converts all components to minutes
  ← int (total minutes)
```

---

## 7. HTTP POST Request

```
Caller: new RequestHelper('https://api.example.com/upload')
  ← RequestHelper (fluent builder)

->setTimeout(30)
->setHeader('Authorization', 'Bearer token')
->addFile('file', 'report.pdf', $pdfContent, 'application/pdf')
->addVariable('description', 'Monthly report')
->send()
  → Builds multipart MIME body
  → curl_exec() with assembled headers and body
  ← string (response body)

->getResponse()    // → RequestHelper_Response (status, headers, body)
```

---

## 8. Localization / Translation

```
// Without application-localization installed:
t('Hello %s', 'World')
  → sprintf('Hello %s', 'World')
  ← 'Hello World'

// With application-localization installed:
t('Hello %s', 'World')
  → \AppLocalize\t('Hello %s', 'World')
  ← translated + interpolated string

// Auto-registration (runs once at Composer autoload time):
init()  [src/functions.php]
  → checks class_exists('\AppLocalize\Localization')
  → if found: Localization::addSourceFolder('application-utils', ...)
              registers localization/ as translation source
```

---

## 9. Class Discovery

```
Caller: ClassHelper::findClassesInRepository(
    FolderInfo::factory('/src/Services'),
    recursive: true,
    instanceOf: ServiceInterface::class
)
  → Delegates to ClassRepositoryManager
  → Scans PHP files via FileFinder
  → PHPClassInfo::getClasses() per file     // no reflection — text parsing
  → Filters by instanceOf using ClassHelper::isClassInstanceOf()
  → Caches results if a cache folder is set
  ← ClassRepository  (iterable collection of matching class names)
```

---

## 10. Number with Units

```
Caller: parseNumber('150px')
  → new NumberInfo('150px')
    → Parses numeric part: 150
    → Parses unit: 'px'
  ← NumberInfo

->getNumber()      // 150
->getUnits()       // 'px'
->isPixels()       // true
->toAttribute()    // '150px'
```
