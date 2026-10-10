---
subtitle: Fenced blocks, indented blocks, and syntax colors
---
# Code

This chapter starts on a new page. `FirstElementInChapterCssClass` put the `chapter-beginning` class on its first element, and the theme gives that class `page-break-before: always`.
{.lead}

`DefaultMarkdownConfiguration` wraps code tokens in `hljs-*` spans. The colors come from rules in `theme/theme.html`.

## Fenced blocks with a language

PHP:

```php
<?php

final class Greeter
{
    public function __construct(private string $name) {}

    // returns a friendly greeting
    public function greet(int $times = 1): string
    {
        return str_repeat("Hello, {$this->name}! ", $times);
    }
}
```

JavaScript:

```js
const chapters = ['intro', 'middle', 'end'];

export function titleCase(word) {
  return word.charAt(0).toUpperCase() + word.slice(1);
}

console.log(chapters.map(titleCase).join(', '));
```

HTML:

```html
<section class="cover">
    <h1>My Book</h1>
    <p>Written in <em>Markdown</em></p>
</section>
```

CSS:

```css
h1.chapter-beginning {
    page-break-before: always;
    color: #1b263b;
}
```

JSON:

```json
{
    "title": "Typesetter Demo Book",
    "chapters": 8,
    "draft": false
}
```

Shell:

```bash
composer require typesetterio/typesetter
php demo/run.php
```

## Fenced block without a language

With no language named, the highlighter guesses from the list passed to `DefaultMarkdownConfiguration`. This book passes `html`, `php`, `js`, `css`, `json`, and `bash`.

```
$total = array_sum([1, 2, 3]);
echo $total;
```

## Indented block

Four spaces of indentation also make a code block:

    function add(a, b) {
        return a + b;
    }

## Keeping blocks together

The theme sets `page-break-inside: avoid` on `pre`, so a code block moves to the next page whole instead of splitting across two.
