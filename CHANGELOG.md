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

### Removed

- The `illuminate/support` and `illuminate/collections` dependencies.

### Fixed

- Content files with the same file name in different directories, or in both `content` and `content-extra`, no longer silently replace each other. `generate()` throws `InvalidConfigException` naming the duplicates.
- The DOM helper used by the bundled observers no longer emits PHP warnings for HTML5 tags such as `<section>` and `<figure>`.
- A chapter containing only an HTML comment no longer crashes the DOM helper.
- The `Finished` event fires only after the PDF is rendered, not when rendering fails.
- The title and author on the generated cover and the `toc-header` text are HTML-escaped.
- An unreadable `theme.html`, `cover.html`, or content file throws a `RuntimeException` naming the file instead of a `TypeError`.
