<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Dom\HTMLDocument;
use Typesetterio\Typesetter\Contracts\Chapter;
use Typesetterio\Typesetter\Observers\Observer;

final class HtmlDocumentObserver extends Observer
{
    public function document(Chapter $chapter): HTMLDocument
    {
        return $this->getHtmlDocument($chapter);
    }

    public function write(Chapter $chapter, HTMLDocument $document): void
    {
        $this->setHtmlFromDocument($chapter, $document);
    }
}
