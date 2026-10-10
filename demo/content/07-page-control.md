---
subtitle: Breaks, margins, and orientation
---
# Page Control

This file is `content/07-page-control.md`. It sits in the content root while chapters 5 and 6 sit in `content/part-two/`, and it still sorts after them.
{.lead}

## Manual page breaks

The `BreakToPageBreak` observer replaces its token, the word BREAK in curly braces, with a page break. The token sits right after this paragraph, so the next heading starts a new page.

{BREAK}

## After the break

This section is on a new page because of the token on the previous page.

## Margins

The library creates every page with fixed margins: 27mm on the sides and 14mm at the top and bottom. Every page so far uses them.

A theme changes margins with named pages. A CSS class is mapped to an `@page` rule with a name, and any block with that class moves to a page with those margins. The next three blocks do this.

<div class="wide-page">

## A wide page

This block has the class `wide-page`, mapped to `@page wide` with 10mm side margins instead of 27mm. The text block is visibly wider than on every other page. A named page always starts on a new page.

Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.

</div>

<div class="landscape-page">

## A landscape page

This block has the class `landscape-page`, mapped to `@page landscape` with `size: landscape`. It suits a table too wide for a portrait page.

| Event | Fires when | Always fires |
|-------|-----------|--------------|
| `Starting` | `generate()` is called | yes |
| `MarkdownInitialized` | the Markdown environment is ready and observers have seen it | yes |
| `PdfInitialized` | the mPDF instance exists | yes |
| `ThemeAdded` | `theme.html` has been written | yes |
| `CoverImageAdded` | `cover.jpg` was used | only with `cover.jpg` |
| `CoverHtmlAdded` | `cover.html` was used | only with `cover.html` |
| `CoverGenerated` | the title and author cover was generated | only without a cover file |
| `TocGenerated` | the table of contents was added | only when `toc-enabled` |
| `FooterGenerated` | the footer was set | only when `footer` is not empty |
| `ContentGenerating` | chapter conversion starts | yes |
| `PdfRendering` | mPDF starts writing the binary | yes |
| `Finished` | the PDF string is ready | yes |

</div>

<div class="standard-page">

## Back to the standard page

This block has the class `standard-page`, mapped to `@page standard`, which repeats the library's portrait size and margins. A named page stays in effect until another one replaces it, so without this block the rest of the book would be landscape.

## The table of contents page

The library gives the table of contents the page name `toc-page`. This theme's `@page toc-page` rule gives it wider side margins than the chapters.

</div>
