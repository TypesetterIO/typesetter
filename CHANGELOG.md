# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0]

### Added

- `toc-levels` config key: the list of heading tags that build the TOC and the PDF outline, default `['H1', 'H2']`.
- `Observer::setHtmlFromDocument()` writes a `Dom\HTMLDocument` body back to the chapter.

### Changed

- Requires PHP 8.4.
- Renamed `Observer::getDomDocument()` to `getHtmlDocument()`; it returns `Dom\HTMLDocument` and the chapter's elements are the body's children.
- `Config::$observers` is an `ObserverCollection` that implements `Countable` and `IteratorAggregate` and no longer extends Laravel's `Collection`.
- Renamed events: `PDFInitialized` to `PdfInitialized`, `PDFRendering` to `PdfRendering`, `TOCGenerated` to `TocGenerated`, `InitializedMarkdown` to `MarkdownInitialized`.
- Renamed observer hooks: `initializedMarkdownEnvironment()` to `markdownEnvironmentInitialized()`, `initializedPdf()` to `pdfInitialized()`, `parsed()` to `chapterParsed()`.
- Renamed the `FirstElementInChapterCSSClass` observer to `FirstElementInChapterCssClass`.
- Renamed exceptions: `TypesetterConfigException` to `InvalidConfigException`, `ListenerInvalidException` to `InvalidListenerException`.
- Renamed config keys: `contentFilter` to `content-filter`, `contentExtra` to `content-extra`.
- `Chapter::setHtml()` and the `Chapter` contract type the parameter as `string|Stringable`.
- `Config` is a `readonly` class.
- `Chapter`, `ObserverCollection`, and every event class are `final`.
- `Typesetter::listen()` with a class that does not implement `Event` throws `InvalidListenerException`.
- `Config` throws `InvalidConfigException` for unknown keys, a missing `content-extra` directory, and `observers` entries that are not `Observer` instances.
- `content-filter` accepts any callable, not only a `Closure`.
- `Chapter::getMetaData()` throws `UnexpectedValueException` when the front matter is a YAML list or has non-string keys, not only when it is a scalar.
- `content-extra` files are filtered by `markdown-extensions`; they still bypass `content-filter`.
- `generate()` throws `InvalidConfigException` when no content files are found.
- A missing or unreadable image, including `cover.jpg`, throws `Mpdf\MpdfImageException` instead of rendering a placeholder.
- PDF outline bookmarks are written even when `toc-enabled` is `false`.
- The TOC's outline entry uses `toc-header` instead of always reading `Contents`.
- `DefaultMarkdownConfiguration` highlights code with `tempest/highlight` `^2.28.1` instead of `spatie/commonmark-highlighter`; Bash and TypeScript fences are highlighted.
- Fenced blocks with no language or an unknown language, and indented code blocks, are plain text.

### Removed

- The `$languagesToProcess` constructor argument of `DefaultMarkdownConfiguration`.
- The `illuminate/support` and `illuminate/collections` dependencies.
- The `ext-mbstring` requirement.

### Fixed

- Content files with the same name in different directories no longer replace each other; `generate()` throws `InvalidConfigException`.
- HTML5 tags no longer trigger PHP warnings in observers that rewrite chapter HTML.
- A chapter containing only an HTML comment no longer crashes those observers.
- `Finished` fires only when rendering succeeds.
- The `footer` prints on pages styled by a theme `@page` rule, bare or named.
- The generated cover's title and author, and the `toc-header` text, are HTML-escaped.
- An unreadable content file throws a `RuntimeException` naming the file.
- Boolean attributes such as the `checked` of a task-list checkbox survive observers that rewrite chapter HTML; the boxes render checked in mPDF.
