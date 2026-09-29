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
        // there is nothing to parse in an empty chapter, and loadHTML() refuses an empty string
        if (trim($chapter->getHtml()) === '') {
            return new DOMDocument('1.0', 'UTF-8');
        }

        $originalDom = new DOMDocument('1.0', 'UTF-8');

        // libxml reads the HTML as ISO-8859-1 unless told otherwise, so non-ASCII goes in as numeric entities
        $html = mb_encode_numericentity($chapter->getHtml(), [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');

        // not doing html/body non-implied because that causes parsing errors in some contexts
        $originalDom->loadHTML($html, LIBXML_HTML_NODEFDTD);

        $resultDom = new DOMDocument('1.0', 'UTF-8');
        foreach ($originalDom->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $resultDom->appendChild($resultDom->importNode($node, true));
        }

        return $resultDom;
    }
}
