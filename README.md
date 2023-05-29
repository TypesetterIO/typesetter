<img src="https://github.com/TypesetterIO/assets/blob/main/logos/v1/logo.png" align="center" height="100">

# Typesetter

This is the Typesetter main service. This can be used in your own projects directly if you want. You probably want to head over to `typesetter-cli` though.

## Install

This requires PHP 8.1 or above.

`composer require typesetterio/typesetter`

## Usage

Create a config array and pass that to the config maker. Then create a new instance of the Typesetter class.  Call the generate method with your config to get a PDF binary return from MPDF.

Example:

```php
$config = [
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
        new \Typesetterio\TypesetterCLI\Observers\FirstElementInChapterCSSClass(),
        new \Typesetterio\TypesetterCLI\Observers\BreakToPageBreak(),
    ],
];

$config = new \Typsetterio\Typesetter\Config($config);
$service = new \Typesetterio\Typesetter\Typesetter();
$pdfContent = $service->generate($config);
file_put_contents('my-pdf.pdf', $pdfContent);
```

### Config Array

The details are as follows:

| Option  | Definition | Example |
|---------| ---------- | ------- |
| `title`  | The Title of your book. | `Benjamin Button` |
| `author` | The author of your book. | `F. Scott Fitzgerald` |
| `theme` | The path to the folder which contains your theme info. This should at minimum have a `theme.html` file in it. | `bb` |
| `toc-enabled` | Should Table of Contents be generated after cover? | `false` |
| `toc-links` | Should table of Contents link to the headers in your document? | `false` |
| `toc-header` | What header with `#toc-header` html attribute text? Empty will not generate one. | `Contents` |
| `footer` | What text should be in the footer? Footer is `<footer class="footer">` element. Leave empty to not generate a footer. | `Page {PAGENO}` |
| `markdown-extensions` | An array of file extensions that indicate they should be rendered. Remember pages are generated in alphabetical or numerical order. | `['md']` |
| `observers` | An array of new instances of observers. You can read more about that below. | See above |

### Observers

Observers allow a decorator or visitor style of design pattern to interact with the process of generating this PDF.  To make things
as transparent as possible, some core items of the service are even configured in observers. 

Observers must implement the `Typesetterio\Typesetter\Contracts\Observer` interface.  You may extend the 
`Typesetterio\Typesetter\Observers\Observer` abstract class so you don't have to define every interface method. Then, you can 
override only the ones you want.

Observers are ran in the order they are registered.

Available observer methods available are the following:

| Method | Definition | Parameters |
| ------ | ---------- | ---------- |
| `initializedMarkdownEnvironment` | After the Commonmark Environment has been initialized, this will allow customization of it. | `League\CommonMark\Environment\Environment` that you can add extensions or renders. |
| `parsed` | After a chapter's markdown has been parsed into HTML and set into a Chapter. The chapter makes methods available to understand the context and modify the HTML. Note that the abstract observer class offers a `getDomDocument()` method that accepts a Chapter and returns a DomDocument. Then you can modify or parse content easier if you'd like. | `Typesetterio\Typesetter\Chapter` |

### Chapter

The `Typesetterio\Typesetter\Chapter` class contains the content of your markdown for that specific chapter.

It contains a number of useful methods for you to interact with the content while it is being converted.

| Method                | Definition                                                                                             |
|-----------------------|--------------------------------------------------------------------------------------------------------|
| `getHtml()` | Get's the rendered HTML from markdown. Remember, this may have been modified by other observers first. |
| `setHtml($html)` | You must set the HTML after you've modified it.  This will cast to string if it's not already. |
| `getChapterNumber()` | Returns an integer indicating which chapter number this is. |
| `getTotalChapters()` | Returns an integer indicating how many total chapters there is to be parsed. |
| `isFirstChapter()` | Returns a boolean indicating if you're dealing with the first chapter. |
| `isLastChapter()` | Returns a boolean indicating if you're dealing with the last chapter. |

If you'd like to just get some status updates or notifications and don't need to modify any content, check out Events.

### Events

During the process of conversion, Typesetter issues some events that you may listen to. This can be useful for giving status updates or creating logs.

In order to register a listener, call the `listen` method with an event class and a callable.

Here's an example:

```php
$service = new \Typesetterio\Typesetter\Typesetter();
$service->listen(\Typesetterio\Typesetter\Events\Finished::class, function () use ($myNotifier) {
    $myNotifier->alert('The process has finished');
});
```

At this time events do not contain any data. If you want to modify anything, please look at observers.

The following events are available (these are all going to be in the `Typesetterio\Typesetter\Events` namespace):

| Event | Definition |
| ----- | ---------- |
| `Starting` | When the `generate()` method is first called. |
| `InitializedMarkdown` | After the markdown environment has been created and all observers have ran on it. |
| `PDFInitialized` | After the MPDF instance has been created and lightly configured. |
| `ThemeAdded` | When the theme has been found and applied to the PDF. |
| `CoverImageAdded` | A cover image has been found in your content and added. |
| `CoverHtmlAdded` | A cover html file has been found in your content and added. |
| `CoverGenerated` | Typesetter was unable to find either cover option so it generated one. |
| `TOCGenerated` | The Table of Contents has been generated and added. |
| `FooterGenerated` | The footer content has been generated and added. |
| `ContentGenerating` | The content has started rendering. This likely is the longest part of the process. |
| `PDFRendering` | The PDF has started rendering from HTML to PDF binary. This may also take a long time. |
| `Finished` | The whole process has finished and a PDF string has been returned from the method. |

You may listen to as many or as little events as you want. Remember, some of these are optional events based on your configuration.

## Themes

Theming support is done with HTML and CSS. The theme folder should contain a `theme.html` which contains a `<style>` declaration
as if it were in the head of an HTML file. This is where you can customize the theme of your PDF. Remember that you will 
be working with print styles in CSS so you can use things like page break.

More to come here!

### Fonts

At this time, Typesetter only supports the fonts that MPDF does. It will support font management in the future.

To see the list of fonts that are available, see the [MPDF Fonts Directory](https://github.com/mpdf/mpdf/tree/v8.1.6/ttfonts).

## Content

The content folder holds the content that is coupled with your config to create the PDF.

@todo

### Cover

There are three options for a cover.

First, you can generate a cover image.  The file should be named `cover.jpg`.  The default configuration for MPDF is 96dpi.  The default page is a A4.  This means a standard
resolution that fits that is 794 x 1123 (96dpi).  You may experiment with different values for larger resolution displays. Remember, the
larger in size your image, the larger the resulting PDF may be.

Second, you can generate a cover with HTML. The file should be named `cover.html`. For more details, reference the HTML processing of [MPDF](https://mpdf.github.io/).

Finally, if you do not create either of these, Typesetter will automatically generate a cover page. It will use your title and author configuration options.

### Chapters

Chapters should be generated in sequentially named files in your content folder. Refer to [League Commonmark](https://commonmark.thephpleague.com/) for
more tips on writing and configuring markdown processors. Out of the box there is a pretty good configuration. This includes the following functionality:

* Github-flavored markdown
* Attributes
* Spatie's Fenced Code / Indented Code renders

## Credits

This was heavily influenced by the [Ibis](https://github.com/themsaid/ibis) project but is a complete rewrite.

This package stands on the shoulders of giants like [MPDF](https://mpdf.github.io/), some parts of [Laravel](https://laravel.com) and also the [League Commonmark](https://commonmark.thephpleague.com/) library.

[Aaron Saray](https://aaronsaray.com) is the primary author and maintainer.
