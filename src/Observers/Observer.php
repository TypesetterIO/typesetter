<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Observers;

use DOMDocument;
use League\CommonMark\Environment\Environment;
use Mpdf\Mpdf;
use Typesetterio\Typesetter\Contracts\Chapter;

abstract class Observer implements \Typesetterio\Typesetter\Contracts\Observer
{
    public function initializedPdf(Mpdf $mpdf): void
    {
    }

    public function initializedMarkdownEnvironment(Environment $environment): void
    {
    }

    public function parsed(Chapter $chapter): void
    {
    }

    public function coverAdded(Mpdf $mpdf): void
    {
    }

    /**
     * Get the DOMDocument for this chapter in the format of an HTML fragment.
     *
     * We do this to make sure that it doesn't always add doctype and html to it.
     */
    protected function getDomDocument(Chapter $chapter): DOMDocument
    {
        $originalDom = new DOMDocument('1.0', 'UTF-8');

        // not doing html/body non-implied because that causes parsing errors in some contexts
        $originalDom->loadHTML(mb_convert_encoding($chapter->getHtml(), 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NODEFDTD);

        $resultDom = new DOMDocument('1.0', 'UTF-8');
        foreach ($originalDom->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $resultDom->appendChild($resultDom->importNode($node, true));
        }

        return $resultDom;
    }
}
