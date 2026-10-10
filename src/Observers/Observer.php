<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Observers;

use Dom\HTMLDocument;
use League\CommonMark\Environment\Environment;
use Mpdf\Mpdf;
use Typesetterio\Typesetter\Contracts\Chapter;

abstract class Observer implements \Typesetterio\Typesetter\Contracts\Observer
{
    public function pdfInitialized(Mpdf $mpdf): void {}

    public function markdownEnvironmentInitialized(Environment $environment): void {}

    public function chapterParsed(Chapter $chapter): void {}

    public function coverAdded(Mpdf $mpdf): void {}

    /**
     * Parse the chapter HTML. The chapter's elements are the children of the document's body.
     */
    protected function getHtmlDocument(Chapter $chapter): HTMLDocument
    {
        return HTMLDocument::createFromString($chapter->getHtml(), LIBXML_NOERROR, 'UTF-8');
    }

    /**
     * Write the document's body back to the chapter as an HTML fragment.
     */
    protected function setHtmlFromDocument(Chapter $chapter, HTMLDocument $document): void
    {
        $chapter->setHtml($document->body->innerHTML); // @phpstan-ignore property.nonObject (createFromString() always sets body)
    }
}
