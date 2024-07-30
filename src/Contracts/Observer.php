<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Contracts;

use League\CommonMark\Environment\Environment;
use Mpdf\Mpdf;

interface Observer
{
    public function parsed(Chapter $chapter): void;

    public function initializedMarkdownEnvironment(Environment $environment): void;

    public function coverAdded(Mpdf $mpdf): void;
}
