<?php

declare(strict_types=1);

namespace Typsetterio\Typesetter;

use Illuminate\Support\Collection;
use League\CommonMark\Environment\Environment;
use Typesetterio\Typesetter\Contracts\Chapter;
use Typesetterio\Typesetter\Contracts\Observer;

class ObserverCollection extends Collection
{
    public function initializedMarkdownEnvironment(Environment $environment): self
    {
        $this->each(fn (Observer $observer) => $observer->initializedMarkdownEnvironment($environment));
        return $this;
    }

    public function parsed(Chapter $chapter): self
    {
        $this->each(fn (Observer $observer) => $observer->parsed($chapter));
        return $this;
    }
}
