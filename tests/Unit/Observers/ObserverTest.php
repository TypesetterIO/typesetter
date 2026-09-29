<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use DOMDocument;
use League\CommonMark\Output\RenderedContent;
use Tests\TestCase;
use Typesetterio\Typesetter\Chapter;
use Typesetterio\Typesetter\Observers\Observer;

class ObserverTest extends TestCase
{
    public function testGetDomDocumentNonAsciiCharactersSurvive(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 1, 1);
        $chapter->setHtml('<p>café €100 — 日本語 🎉</p><p title="naïve">two</p>');

        $observer = new class extends Observer {
            public function dom(Chapter $chapter): DOMDocument
            {
                return $this->getDomDocument($chapter);
            }
        };

        self::assertEquals(
            '<p>café €100 — 日本語 🎉</p><p title="naïve">two</p>',
            html_entity_decode(trim($observer->dom($chapter)->saveHTML()), ENT_QUOTES | ENT_HTML5, 'UTF-8')
        );
    }
}
