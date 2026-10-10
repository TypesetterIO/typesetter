---
subtitle: Supplied by content-extra
---
# Appendix

This chapter is not in `content`. It lives in `demo/content-extra/`, the directory named by the `content-extra` config key.
{.lead}

Files from `content-extra` are merged with the main content and sorted by file name with everything else. This one is named `90-appendix.md` so it sorts last. They are filtered by `markdown-extensions` and they bypass `content-filter`.

## Credits

The box below is not in this file. The `Credits` observer appends it to the last chapter of the book, and the theme styles its `credits-box` class.
