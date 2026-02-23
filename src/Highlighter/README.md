# Highlighter Module

Convenience wrapper around the [highlight.php](https://github.com/scrivo/highlight.php) library to add syntax highlighting to strings or files. Supports a large number of languages, and has two output modes: fully self-contained inline styles (the default) or class-based CSS.

It's designed to be fire-and-forget.

---

## Quick Start

```php
use AppUtils\Highlighter;

// Highlight a PHP string (inline styles enabled by default)
echo Highlighter::php('<?php echo "Hello"; ?>');

// Highlight from a file
echo Highlighter::parseFile('/path/to/query.sql', 'sql');

// Switch to class-based output + external stylesheet
Highlighter::setUseInlineStyles(false);
echo Highlighter::getStyleTag();          // include once per page
echo Highlighter::parseString($xml, 'xml');
```

---

## Output Modes

| Mode | Enabled via | Description |
|---|---|---|
| **Inline styles** (default) | `Highlighter::setUseInlineStyles(true)` | Every `<span>` gets a `style` attribute. No external CSS needed. Ideal for emails and PDFs. |
| **Class-based CSS** | `Highlighter::setUseInlineStyles(false)` | Output uses `hljs-*` CSS classes. Include `Highlighter::getStyleTag()` (or serve the CSS yourself) once per page. |

---

## Theme Configuration

```php
Highlighter::setDefaultTheme('atom-one-dark');  // any highlight.js theme name
Highlighter::getAvailableThemes();              // list all available theme names
Highlighter::getStyleCSS();                     // raw CSS for the current theme
```

The default theme is **`github`**.

### Configuration

| Method | Description |
|---|---|
| `setDefaultTheme(string $name): void` | Set the highlight.js theme. |
| `getDefaultTheme(): string` | Get the current theme name. |
| `getAvailableThemes(): string[]` | List all installed themes. |
| `setUseInlineStyles(bool $enabled): void` | Toggle inline-style mode. |
| `isUseInlineStyles(): bool` | Query inline-style mode state. |
| `getStyleTag(): string` | `<style>` tag with the current theme's CSS. |
| `getStyleCSS(): string` | Raw CSS string for the current theme. |
| `resetConfig(): void` | Restore all settings to defaults (for test teardown). |

