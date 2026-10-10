---
subtitle: GitHub-flavored Markdown, styled by the theme
---
# Text Basics

This paragraph opens the first chapter. The `FirstElementInChapterCssClass` observer skips the first chapter by default, so nothing here has the `chapter-beginning` class. The chapter still starts on a fresh page because the table of contents ends with a page break.
{.lead}

The "Chapter 1 of 8" label above the heading and the subtitle below it are not in this Markdown file. `DemoObserver` adds them; chapter 5 explains how.

## Inline formatting

Body text can be *emphasized*, **strong**, or ***both***. It can carry `inline code`, ~~strikethrough~~, and a [link to typesetter.io](https://typesetter.io). A bare URL such as https://github.com/TypesetterIO becomes a link on its own.

Non-ASCII text survives the trip: café, naïve, Zürich, “curly quotes”, an ellipsis…, and symbols like © and €.

## Lists

An unordered list with nesting:

- Markdown goes in
- Observers adjust it
    - before the PDF exists
    - after each chapter is parsed
- A PDF comes out

An ordered list:

1. Write chapters
2. Write a theme
3. Generate

## Blockquotes and rules

> A plain blockquote. The theme gives it a left border and italic text.
> Chapter 4 turns blockquotes into colored callouts.

A horizontal rule follows this paragraph.

---

### A third-level heading

The `toc-levels` config for this book is `['H1', 'H2', 'H3']`, so this heading appears in the table of contents and in the PDF outline, indented under "Blockquotes and rules".

#### A fourth-level heading

H4 is not in `toc-levels`. This heading is styled, and it is missing from the table of contents.
