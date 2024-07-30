<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter;

use Illuminate\Support\Collection;
use League\CommonMark\Environment\Environment;
use Mpdf\Mpdf;
use Typesetterio\Typesetter\Contracts\Chapter;
use Typesetterio\Typesetter\Contracts\Observer;

class ObserverCollection extends Collection
{
    public function initializedMarkdownEnvironment(Environment $environment): self
    {
        $this->each(fn (Observer $observer) => $observer->initializedMarkdownEnvironment($environment));
        return $this;
    }

    public function coverAdded(Mpdf $mpdf): self
    {
        $this->each(fn (Observer $observer) => $observer->coverAdded($mpdf));
    }

    public function parsed(Chapter $chapter): self
    {
        $this->each(fn (Observer $observer) => $observer->parsed($chapter));
        return $this;
    }
}
