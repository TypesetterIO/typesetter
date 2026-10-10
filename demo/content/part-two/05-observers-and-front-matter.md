---
subtitle: This subtitle is the front matter of this file
reviewed: true
---
# Observers and Front Matter

This file lives in `content/part-two/`. Content directories are read recursively and chapters are ordered by file name alone, so `05-…` follows `04-…` and `07-…` follows `06-…` no matter which folder holds each one.
{.lead}

## Front matter

This file starts with a YAML block:

```
---
subtitle: This subtitle is the front matter of this file
reviewed: true
---
```

The block is not printed. An observer reads it with `$chapter->getMetaData()`. `DemoObserver` takes the `subtitle` value and inserts it under the chapter heading, which is where the line above came from.

## A custom observer

`demo/DemoObserver.php` extends the base `Observer` class and uses all four hooks:

| Hook | What `DemoObserver` does with it |
|------|----------------------------------|
| `markdownEnvironmentInitialized` | Registers the CommonMark footnote extension |
| `pdfInitialized` | Sets the PDF subject and keywords and the DEMO watermark |
| `coverAdded` | Writes the page between the cover and the table of contents |
| `chapterParsed` | Adds the "Chapter N of M" label and the subtitle |

In `chapterParsed` it calls `getChapterNumber()` and `getTotalChapters()` for the label, and the base class's `getDomDocument()` helper to place the new elements around the H1.

Observers run in the order they are listed in the config. `DemoObserver` is listed before `FirstElementInChapterCssClass`, so the label it adds is the first element and receives the `chapter-beginning` class.

## Footnotes

Footnotes are not on by default. `DemoObserver` turns them on, so this sentence has a footnote marker[^first] and so does this one[^second]. The notes print at the end of the chapter.

[^first]: The first footnote. It was written at the bottom of the Markdown file.
[^second]: The second footnote, with *formatting* and `code`.

## The bundled observers

| Observer | Seen in this book |
|----------|-------------------|
| `DefaultMarkdownConfiguration` | Code colors, attributes, front matter |
| `FirstElementInChapterCssClass` | Every chapter after the first starts a new page |
| `BreakToPageBreak` | The manual break in chapter 7 |
| `Credits` | The box at the very end of the book |
