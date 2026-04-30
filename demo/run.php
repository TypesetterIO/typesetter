<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Typesetterio\Typesetter\Config;
use Typesetterio\Typesetter\Observers\BreakToPageBreak;
use Typesetterio\Typesetter\Observers\Credits;
use Typesetterio\Typesetter\Observers\DefaultMarkdownConfiguration;
use Typesetterio\Typesetter\Observers\FirstElementInChapterCSSClass;
use Typesetterio\Typesetter\Typesetter;

$config = new Config([
    'title' => 'Typesetter Demo Book',
    'author' => 'Aaron Saray',
    'theme' => __DIR__ . '/theme',
    'content' => __DIR__ . '/content',
    'observers' => [
        new DefaultMarkdownConfiguration(),
        new FirstElementInChapterCSSClass(),
        new BreakToPageBreak(),
        new Credits(),
    ],
]);

$service = new Typesetter();
$pdfContent = $service->generate($config);

$outputPath = __DIR__ . '/output.pdf';
file_put_contents($outputPath, $pdfContent);

printf("Wrote %d bytes to %s\n", strlen($pdfContent), $outputPath);
