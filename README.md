<p align="center">
    <img src=".github/assets/logo.png">
</p>
<p align="center">
    <img src="https://img.shields.io/github/license/typesetterio/typesetter?labelColor=e7e5e4&color=292524" alt="License">
    <img src="https://img.shields.io/packagist/dm/typesetterio/typesetter?labelColor=e7e5e4&color=292524" alt="Packagist">
    <img src="https://img.shields.io/github/actions/workflow/status/typesetterio/typesetter/ci.yml?labelColor=e7e5e4&color=292524" alt="GitHub Workflow Status">
</p>

# Typesetter

This is the Typesetter main service. This can be used in your own projects directly if you want. You probably want to head over to [typesetter-cli](https://github.com/typesetterio/typesetter-cli) though.

## Install

This requires PHP 8.3 or above.

`composer require typesetterio/typesetter`

## Usage

Build a `Config` from an array of options, then pass it to `Typesetter::generate()` to get a PDF binary back from MPDF.

Example:

```php
$config = new \Typesetterio\Typesetter\Config([
    'title' => 'Benjamin Button',
    'author' => 'F. Scott Fitzgerald',
    'theme' => 'bb',

    'toc-enabled' => true,
    'toc-links' => true,
    'toc-header' => 'Table of Contents',

    'footer' => '{PAGENO}',

    'markdown-extensions' => ['md', 'markdown'],
    'observers' => [
        new \Typesetterio\Typesetter\Observers\DefaultMarkdownConfiguration(),
        new \Typesetterio\Typesetter\Observers\FirstElementInChapterCSSClass(),
        new \Typesetterio\Typesetter\Observers\BreakToPageBreak(),
        new \Typesetterio\Typesetter\Observers\Credits(),
    ],
]);

$service = new \Typesetterio\Typesetter\Typesetter();
$pdfContent = $service->generate($config);
file_put_contents('my-pdf.pdf', $pdfContent);
```

To learn more, please check out the [documentation](https://typesetter.io). This details configuration, customization, themes and cover generation, observers, listeners and more.

## Demo

The `demo/` directory contains a runnable example used for manual smoke testing. It includes a small theme, three Markdown chapters that exercise each bundled observer, and `demo/run.php`, which generates a PDF at `demo/output.pdf` (gitignored). Run it from the project root with `php demo/run.php`.

## Credits

This was heavily influenced by the [Ibis](https://github.com/themsaid/ibis) project but is a complete rewrite.

This package stands on the shoulders of giants like [MPDF](https://mpdf.github.io/), some parts of [Laravel](https://laravel.com) and also the [League Commonmark](https://commonmark.thephpleague.com/) library.

[Aaron Saray](https://aaronsaray.com) is the primary author and maintainer.
