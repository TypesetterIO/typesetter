# The Middle Chapter

This chapter exists to verify that the `FirstElementInChapterCSSClass` observer
applies its class to the first element of non-first chapters. The proof is
that this chapter starts on its own page, because the theme applies
`page-break-before` to the `chapter-beginning` class.

{BREAK}

After the manual break above, this paragraph should appear on a new page. The
`BreakToPageBreak` observer replaces the break token (the word BREAK
surrounded by curly braces) with a real page-break div.

## Middle section heading

Some more body text to give the page some weight. Lorem ipsum dolor sit amet,
consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et
dolore magna aliqua.
