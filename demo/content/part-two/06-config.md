---
subtitle: What demo/run.php sets, and what it leaves out
---
# Config

## Options this book sets

| Key | Value | Where to see it |
|-----|-------|-----------------|
| `title`, `author` | Typesetter Demo Book, Aaron Saray | PDF document properties |
| `theme` | `theme` | Every page |
| `content` | `content` | Chapters 1 to 7 |
| `content-filter` | Rejects file names starting with `draft-` | See below |
| `content-extra` | `content-extra` | The appendix |
| `toc-enabled` | `true` | The table of contents |
| `toc-links` | `true` | TOC entries are clickable |
| `toc-header` | Demo Table of Contents | TOC heading and its outline entry |
| `toc-levels` | H1, H2, H3 | TOC depth and the PDF outline |
| `footer` | Book title, then `{PAGENO} of {nbpg}` | Bottom of each content page |
| `markdown-extensions` | `['md']` | See below |
| `observers` | Four bundled plus `DemoObserver` | Chapter 5 |

## Files that are skipped on purpose

The content directory holds two files that must not appear in this book:

- `content/draft-08-unfinished.md` is rejected by `content-filter`. If a chapter titled "Unfinished Draft" shows up, the filter is broken.
- `content/notes.txt` is ignored because `txt` is not in `markdown-extensions`.

The label at the top of this chapter reads "of 8". That count is seven chapters from `content` plus the appendix, with neither skipped file included.

## The cover

The cover is `theme/cover.html`. Its title and author are written in that file; they are not read from the config.

## Events

`demo/run.php` registers a listener for every event with `Typesetter::listen()` and prints a line for each as it fires. In this book's configuration, `CoverImageAdded` and `CoverGenerated` never fire.

## Not shown in this book

These options conflict with ones the book uses, so they are left out:

- `toc-enabled` set to `false`: no table of contents. The PDF outline is still written.
- `toc-links` set to `false`: TOC entries are plain text.
- `toc-header` set to an empty string: no heading above the TOC.
- `footer` set to an empty string: no footer.
- `theme/cover.jpg`: a full-page image cover. It wins over `cover.html` when both exist.
- No cover file at all: a generated cover with the title and author.
- `FirstElementInChapterCssClass` with `skipFirst: false`: the first chapter gets the class too.
- Custom arguments for `BreakToPageBreak` (a different token) and `Credits` (a different class).
