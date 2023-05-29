<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use Tests\TestCase;
use Typesetterio\Typesetter\Chapter;
use Typesetterio\Typesetter\Observers\BreakToPageBreak;

class BreakToPageBreakTest extends TestCase
{
    public function testDoesNothingIfNotFound(): void
    {
        $chapter = new Chapter('', 0, 0);
        $chapter->setHtml('<p>I am a tag</p><div>Another</div>');

        $observer = new BreakToPageBreak();
        $observer->parsed($chapter);
        self::assertEquals('<p>I am a tag</p><div>Another</div>', $chapter->getHtml());
    }

    public function testReplacesOnDefault(): void
    {
        $chapter = new Chapter('', 0, 0);
        $chapter->setHtml('<p>one</p><p>{BREAK}</p><div>two</div>{BREAK}<p>three</p>');

        $observer = new BreakToPageBreak();
        $observer->parsed($chapter);
        self::assertEquals(
            '<p>one</p><p><div style="page-break-after:always"></div></p><div>two</div><div style="page-break-after:always"></div><p>three</p>',
            $chapter->getHtml()
        );
    }

    public function testReplacesWithUpdated(): void
    {
        $chapter = new Chapter('', 0, 0);
        $chapter->setHtml('<p>one</p><p>{BREAK}</p><div>two</div>{HONK}<p>three</p>');

        $observer = new BreakToPageBreak('{HONK}');
        $observer->parsed($chapter);
        self::assertEquals(
            '<p>one</p><p>{BREAK}</p><div>two</div><div style="page-break-after:always"></div><p>three</p>',
            $chapter->getHtml()
        );
    }
}
