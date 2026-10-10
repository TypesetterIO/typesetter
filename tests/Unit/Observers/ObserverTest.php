<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use League\CommonMark\Output\RenderedContent;
use Tests\Fixtures\HtmlDocumentObserver;
use Tests\TestCase;
use Typesetterio\Typesetter\Chapter;

class ObserverTest extends TestCase
{
    public function testGetHtmlDocumentNonAsciiCharactersSurvive(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 1, 1);
        $chapter->setHtml('<p>café €100 — 日本語 🎉</p><p title="naïve">two</p>');

        $document = (new HtmlDocumentObserver())->document($chapter);

        self::assertSame('<p>café €100 — 日本語 🎉</p><p title="naïve">two</p>', $document->body->innerHTML); // @phpstan-ignore property.nonObject (createFromString() always sets body)
    }

    public function testRoundTripKeepsBooleanAttributeValues(): void
    {
        $html = "<ul>\n<li><input checked=\"\" disabled=\"\" type=\"checkbox\"> done</li>\n</ul>\n";
        $chapter = new Chapter($this->createStub(RenderedContent::class), 1, 1);
        $chapter->setHtml($html);

        $observer = new HtmlDocumentObserver();
        $observer->write($chapter, $observer->document($chapter));

        self::assertSame($html, $chapter->getHtml());
    }
}
