# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - Unreleased

### Changed

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

### Removed

- The `illuminate/support` and `illuminate/collections` dependencies.

### Fixed

- Content files with the same name in different directories no longer replace each other; `generate()` throws `InvalidConfigException`.
- HTML5 tags no longer trigger PHP warnings in observers that rewrite chapter HTML.
- A chapter containing only an HTML comment no longer crashes those observers.
- `Finished` fires only when rendering succeeds.
- The generated cover's title and author, and the `toc-header` text, are HTML-escaped.
- An unreadable `theme.html`, `cover.html`, or content file throws a `RuntimeException` naming the file.
