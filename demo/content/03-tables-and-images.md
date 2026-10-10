---
subtitle: Pipe tables and pictures
---
# Tables and Images

## Tables

A GitHub-flavored pipe table. The theme adds the dark header row, the zebra stripes, and the full width. The third column is right-aligned in the Markdown with `---:`.

| Config key | Purpose | Default |
|:-----------|:--------|--------:|
| `title` | Book title, used for PDF metadata | My Typeset Book |
| `author` | Author, used for PDF metadata | Joey Bubblegum |
| `toc-enabled` | Add a table of contents | true |
| `toc-links` | Link TOC entries to their pages | true |
| `footer` | Footer text on content pages | {PAGENO} |

A table can take a class through the attributes syntax. This one has `{.compact}` on the line above it, which the theme renders narrower and without stripes.

{.compact}
| Hook | Runs |
|------|------|
| `markdownEnvironmentInitialized` | once |
| `pdfInitialized` | once |
| `coverAdded` | once |
| `chapterParsed` | per chapter |

## Images

Image paths resolve from the directory the generator runs in. `demo/run.php` changes into `demo/`, so this image is written as `content/images/pipeline.png`.

![The Typesetter pipeline: Markdown, then Typesetter, then PDF](content/images/pipeline.png){.figure}

Figure 1. The image has the class `figure` and this caption paragraph has the class `caption`, both set with attributes.
{.caption}

A missing or broken image stops generation with an `Mpdf\MpdfImageException` instead of printing a placeholder.

The same image, sized inline with an attribute: ![Small pipeline](content/images/pipeline.png){width=120}
