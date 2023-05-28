<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Contracts;

use League\CommonMark\Environment\Environment;

interface Observer
{
    public function parsed(Chapter $chapter): void;

    public function initializedMarkdownEnvironment(Environment $environment): void;
}
