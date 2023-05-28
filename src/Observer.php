<?php

declare(strict_types=1);

namespace Typsetterio\Typesetter;

use DOMDocument;
use League\CommonMark\Environment\Environment;
use Typesetterio\Typesetter\Contracts\Chapter;

abstract class Observer implements \Typesetterio\Typesetter\Contracts\Observer
{
    public function initializedMarkdownEnvironment(Environment $environment): void
    {
    }

    public function parsed(Chapter $chapter): void
    {
    }

    /**
     * Get the DOMDocument for this chapter in the format of an HTML fragment.
     *
     * We do this to make sure that it doesn't always add doctype and html to it.
     */
    protected function getDomDocument(Chapter $chapter): DOMDocument
    {
        $dom = new DOMDocument();
        $fragment = $dom->createDocumentFragment();
        $fragment->appendXml($chapter->getHtml());
        $dom->appendChild($fragment);
        return $dom;
    }
}
