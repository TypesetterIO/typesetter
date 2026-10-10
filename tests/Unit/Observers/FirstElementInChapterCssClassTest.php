<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use League\CommonMark\Output\RenderedContent;
use Tests\TestCase;
use Typesetterio\Typesetter\Chapter;
use Typesetterio\Typesetter\Observers\FirstElementInChapterCssClass;

class FirstElementInChapterCssClassTest extends TestCase
{
    public function testNothingHappensWhenFirstChapterByDefault(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 1, 2);
        $chapter->setHtml('<p>one</p><p>two</p>');

        $observer = new FirstElementInChapterCssClass();
        $observer->chapterParsed($chapter);

        self::assertEquals('<p>one</p><p>two</p>', $chapter->getHtml());
    }

    public function testClassAddedToFirstWhenSaid(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 1, 2);
        $chapter->setHtml('<p>one</p><p>two</p>');

        $observer = new FirstElementInChapterCssClass(skipFirst: false);
        $observer->chapterParsed($chapter);

        self::assertEquals('<p class="chapter-beginning">one</p><p>two</p>', trim($chapter->getHtml()));
    }

    public function testClassIsMerged(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 2, 2);
        $chapter->setHtml('<p class="already-here">one</p><p>two</p>');

        $observer = new FirstElementInChapterCssClass();
        $observer->chapterParsed($chapter);

        self::assertEquals('<p class="already-here chapter-beginning">one</p><p>two</p>', trim($chapter->getHtml()));
    }

    public function testClassChangedRenders(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 2, 2);
        $chapter->setHtml('<p>one</p><p>two</p>');

        $observer = new FirstElementInChapterCssClass(class: 'special');
        $observer->chapterParsed($chapter);

        self::assertEquals('<p class="special">one</p><p>two</p>', trim($chapter->getHtml()));
    }

    public function testEmptyChapterStaysEmpty(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 2, 2);
        $chapter->setHtml('');

        $observer = new FirstElementInChapterCssClass();
        $observer->chapterParsed($chapter);

        self::assertEquals('', $chapter->getHtml());
    }

    public function testClassAddedToWrappingElement(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 2, 2);
        $chapter->setHtml('<section><h1>x</h1></section><p>y</p>');

        $observer = new FirstElementInChapterCssClass();
        $observer->chapterParsed($chapter);

        self::assertEquals('<section class="chapter-beginning"><h1>x</h1></section><p>y</p>', trim($chapter->getHtml()));
    }

    public function testCommentOnlyChapterIsUnchanged(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 2, 2);
        $chapter->setHtml('<!-- todo -->');

        $observer = new FirstElementInChapterCssClass();
        $observer->chapterParsed($chapter);

        self::assertEquals('<!-- todo -->', $chapter->getHtml());
    }
}
