<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use Tests\TestCase;
use Typesetterio\Typesetter\Chapter;
use Typesetterio\Typesetter\Observers\Credits;

class CreditsTest extends TestCase
{
    public function testNothingHappensWhenNotLastChapter(): void
    {
        $chapter = new Chapter('', 1, 2);
        $chapter->setHtml('<p>one</p><p>two</p>');

        $observer = new Credits();
        $observer->parsed($chapter);

        self::assertEquals('<p>one</p><p>two</p>', $chapter->getHtml());
    }

    public function testCreditsAddedToLastPageDefaultClass(): void
    {
        $chapter = new Chapter('', 2, 2);
        $chapter->setHtml('<p>one</p><p>two</p>');

        $observer = new Credits();
        $observer->parsed($chapter);

        self::assertEquals(
            '<p>one</p><p>two</p><div class="credits-box">Created using <a href="https:://typesetter.io">Typesetter.io</a></div>',
            $chapter->getHtml()
        );
    }

    public function testCreditsAddedToLastPageSpecifiedClass(): void
    {
        $chapter = new Chapter('', 3, 3);
        $chapter->setHtml('<p>a</p><p>b</p>');

        $observer = new Credits(class: 'derp');
        $observer->parsed($chapter);

        self::assertEquals(
            '<p>a</p><p>b</p><div class="derp">Created using <a href="https:://typesetter.io">Typesetter.io</a></div>',
            $chapter->getHtml()
        );
    }
}
