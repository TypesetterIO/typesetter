---
subtitle: Classes and ids from Markdown, and raw HTML
---
# Attributes and Callouts

The attributes extension is part of `DefaultMarkdownConfiguration`. A line such as `{.notice}` adds a class to the block next to it, and `{#some-id}` adds an id. The theme decides what each class looks like.
{.lead}

## Callouts

Each box below is an ordinary blockquote with a class.

{.notice}
> **Notice**
> This blockquote has the class `notice`. It should be blue with a blue left border.

{.tip}
> **Tip**
> This blockquote has the class `tip`. It should be green with a green left border.

{.warning}
> **Warning**
> This blockquote has the class `warning`. It should be red with a red left border.

{.pull-quote}
> This blockquote has the class `pull-quote`. It should be large, centered, and ruled above and below.

## Inline attributes

Attributes also attach to inline elements: this **strong text**{.badge} has the class `badge`, and this [link](https://typesetter.io){.button} has the class `button`.

## Ids and internal links {#ids-and-links}

This section's heading has the id `ids-and-links`. Because of that, [this link](#ids-and-links) jumps back to it from anywhere in the PDF.

## Raw HTML

HTML written directly in a chapter passes through to the PDF. The two-cell layout below is a `<table class="split">` typed into the Markdown file.

<table class="split">
<tr>
<td class="split-left">Left cell<br>Plain HTML, styled by the theme.</td>
<td class="split-right">Right cell<br>Useful for layouts Markdown cannot express.</td>
</tr>
</table>

<div class="boxed">

This paragraph is Markdown inside a `<div class="boxed">`. Blank lines around the content let *Markdown* keep working inside the HTML wrapper.

</div>
