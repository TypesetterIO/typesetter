<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/DemoObserver.php';

use Typesetterio\Typesetter\Config;
use Typesetterio\Typesetter\Events;
use Typesetterio\Typesetter\Observers\BreakToPageBreak;
use Typesetterio\Typesetter\Observers\Credits;
use Typesetterio\Typesetter\Observers\DefaultMarkdownConfiguration;
use Typesetterio\Typesetter\Observers\FirstElementInChapterCssClass;
use Typesetterio\Typesetter\Typesetter;

chdir(__DIR__);

$config = new Config([
    'title' => 'Typesetter Demo Book',
    'author' => 'Aaron Saray',

    'theme' => 'theme',
    'content' => 'content',
    'content-filter' => fn(SplFileInfo $file, string $fileName): bool => !str_starts_with($fileName, 'draft-'),
    'content-extra' => 'content-extra',

    'toc-enabled' => true,
    'toc-links' => true,
    'toc-header' => 'Demo Table of Contents',
    'toc-levels' => ['H1', 'H2', 'H3'],

    'footer' => 'Typesetter Demo Book  |  {PAGENO} of {nbpg}',

    'markdown-extensions' => ['md'],

    'observers' => [
        new DefaultMarkdownConfiguration(['html', 'php', 'js', 'css', 'json', 'bash']),
        new DemoObserver(),
        new FirstElementInChapterCssClass(),
        new BreakToPageBreak(),
        new Credits(),
    ],
]);

$service = new Typesetter();

$progress = [
    Events\Starting::class => 'Starting',
    Events\MarkdownInitialized::class => 'Markdown environment ready',
    Events\PdfInitialized::class => 'mPDF ready',
    Events\ThemeAdded::class => 'Theme added',
    Events\CoverImageAdded::class => 'Cover added from cover.jpg',
    Events\CoverHtmlAdded::class => 'Cover added from cover.html',
    Events\CoverGenerated::class => 'Cover generated from title and author',
    Events\TocGenerated::class => 'Table of contents added',
    Events\FooterGenerated::class => 'Footer added',
    Events\ContentGenerating::class => 'Converting chapters',
    Events\PdfRendering::class => 'Rendering PDF',
    Events\Finished::class => 'Finished',
];
foreach ($progress as $eventClass => $message) {
    $service->listen($eventClass, function () use ($message): void {
        echo $message, "\n";
    });
}

$pdfContent = $service->generate($config);

$outputPath = __DIR__ . '/output.pdf';
file_put_contents($outputPath, $pdfContent);

printf("Wrote %d bytes to %s\n", strlen($pdfContent), $outputPath);
