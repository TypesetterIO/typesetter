<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use Tests\TestCase;
use Typesetterio\Typesetter\Chapter;
use Typesetterio\Typesetter\Observers\FirstElementInChapterCSSClass;

class FirstElementInChapterCSSClassTest extends TestCase
{
    public function testNothingHappensWhenFirstChapterByDefault(): void
    {
        $chapter = new Chapter('', 1, 2);
        $chapter->setHtml('<p>one</p><p>two</p>');

        $observer = new FirstElementInChapterCSSClass();
        $observer->parsed($chapter);

        self::assertEquals('<p>one</p><p>two</p>', $chapter->getHtml());
    }

    public function testClassAddedToFirstWhenSaid(): void
    {
        $chapter = new Chapter('', 1, 2);
        $chapter->setHtml('<p>one</p><p>two</p>');

        $observer = new FirstElementInChapterCSSClass(skipFirst: false);
        $observer->parsed($chapter);

        self::assertEquals('<p class="chapter-beginning">one</p><p>two</p>', trim($chapter->getHtml()));
    }

    public function testClassIsMerged(): void
    {
        $chapter = new Chapter('', 2, 2);
        $chapter->setHtml('<p class="already-here">one</p><p>two</p>');

        $observer = new FirstElementInChapterCSSClass();
        $observer->parsed($chapter);

        self::assertEquals('<p class="already-here chapter-beginning">one</p><p>two</p>', trim($chapter->getHtml()));
    }

    public function testClassChangedRenders(): void
    {
        $chapter = new Chapter('', 2, 2);
        $chapter->setHtml('<p>one</p><p>two</p>');

        $observer = new FirstElementInChapterCSSClass(class: 'special');
        $observer->parsed($chapter);

        self::assertEquals('<p class="special">one</p><p>two</p>', trim($chapter->getHtml()));
    }
}
