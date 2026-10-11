<?php

declare(strict_types=1);

namespace Tests\Unit;

use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Output\RenderedContent;
use Stringable;
use Tests\TestCase;
use Typesetterio\Typesetter\Chapter;
use UnexpectedValueException;

class ChapterTest extends TestCase
{
    public function testGetMetaDataReturnsFrontMatter(): void
    {
        $chapter = new Chapter(new RenderedContentWithFrontMatter(new Document(), '<p>x</p>', ['title' => 'T']), 1, 1);

        self::assertSame(['title' => 'T'], $chapter->getMetaData());
    }

    public function testGetMetaDataIsEmptyWithoutFrontMatter(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 1, 1);

        self::assertSame([], $chapter->getMetaData());
    }

    public function testScalarFrontMatterThrowsException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageIs('Front matter must be a YAML mapping');

        (new Chapter(new RenderedContentWithFrontMatter(new Document(), '<p>x</p>', 'hello'), 1, 1))->getMetaData();
    }

    public function testSequenceFrontMatterThrowsException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageIs('Front matter must be a YAML mapping');

        (new Chapter(new RenderedContentWithFrontMatter(new Document(), '<p>x</p>', ['a', 'b']), 1, 1))->getMetaData();
    }

    public function testGetHtmlIsTheRenderedContent(): void
    {
        $chapter = new Chapter(new RenderedContent(new Document(), '<p>x</p>'), 1, 1);

        self::assertSame('<p>x</p>', $chapter->getHtml());
    }

    public function testSetHtmlAcceptsStringable(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 1, 1);
        $chapter->setHtml(new class implements Stringable {
            public function __toString(): string
            {
                return '<p>y</p>';
            }
        });

        self::assertSame('<p>y</p>', $chapter->getHtml());
    }

    public function testSingleChapterIsFirstAndLast(): void
    {
        $chapter = new Chapter($this->createStub(RenderedContent::class), 1, 1);

        self::assertSame(1, $chapter->getChapterNumber());
        self::assertSame(1, $chapter->getTotalChapters());
        self::assertTrue($chapter->isFirstChapter());
        self::assertTrue($chapter->isLastChapter());
    }
}
