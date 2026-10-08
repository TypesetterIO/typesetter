<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Contracts;

use League\CommonMark\Environment\Environment;
use Mpdf\Mpdf;

interface Observer
{
    public function chapterParsed(Chapter $chapter): void;

    public function markdownEnvironmentInitialized(Environment $environment): void;

    public function pdfInitialized(Mpdf $mpdf): void;

    public function coverAdded(Mpdf $mpdf): void;
}
