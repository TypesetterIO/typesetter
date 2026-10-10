<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Typesetterio\Typesetter\Config;
use Typesetterio\Typesetter\Contracts\Chapter;
use Typesetterio\Typesetter\Contracts\Event;
use Typesetterio\Typesetter\Contracts\Observer;
use Typesetterio\Typesetter\Events\ContentGenerating;
use Typesetterio\Typesetter\Events\CoverHtmlAdded;
use Typesetterio\Typesetter\Events\CoverImageAdded;
use Typesetterio\Typesetter\Events\Finished;
use Typesetterio\Typesetter\Events\FooterGenerated;
use Typesetterio\Typesetter\Events\MarkdownInitialized;
use Typesetterio\Typesetter\Events\PdfInitialized;
use Typesetterio\Typesetter\Events\PdfRendering;
use Typesetterio\Typesetter\Events\Starting;
use Typesetterio\Typesetter\Events\ThemeAdded;
use Typesetterio\Typesetter\Events\TocGenerated;
use Typesetterio\Typesetter\Exceptions\InvalidConfigException;
use Typesetterio\Typesetter\Exceptions\InvalidListenerException;
use Typesetterio\Typesetter\Typesetter;

class TypesetterTest extends TestCase
{
    public function testRegisterListenerNoImplementationsThrowsException(): void
    {
        $this->expectException(InvalidListenerException::class);
        $this->expectExceptionMessage('not-a-class does not implement Typesetterio\Typesetter\Contracts\Event');

        $typesetter = new Typesetter();
        $typesetter->listen('not-a-class', function () {
        });
    }

    public function testRegisterListenersNotImplementEventContractThrowsException(): void
    {
        $this->expectException(InvalidListenerException::class);
        $this->expectExceptionMessage('Typesetterio\Typesetter\Contracts\Observer does not implement Typesetterio\Typesetter\Contracts\Event');

        $typesetter = new Typesetter();
        $typesetter->listen(Observer::class, function () {
        });
    }

    public function testNoContentFilesThrowsException(): void
    {
        $theme = __DIR__ . '/../data/theme';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('No content files found in ' . $theme);

        (new Typesetter())->generate(new Config([
            'theme' => $theme,
            'content' => $theme,
        ]));
    }

    public function testBookmarksAreWrittenWhenTocIsDisabled(): void
    {
        $result = (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'toc-enabled' => false,
        ]));

        self::assertStringContainsString('/Outlines', $result);
    }

    public function testHtmlCoverWithTocNoTocHeaderWithFooterWithFiles(): void
    {
        $observerForParsed = new class extends \Typesetterio\Typesetter\Observers\Observer {
            public function __construct(public int $times = 0)
            {
            }

            public function chapterParsed(Chapter $chapter): void
            {
                $this->times++;
            }
        };

        $theme = __DIR__ . '/../data/another-theme';
        $content = __DIR__ . '/../data/content';
        $config = new Config([
            'theme' => $theme,
            'content' => $content,
            'title' => 'The title',
            'author' => 'The author',
            'observers' => [
                $observerForParsed,
            ],
        ]);

        $typesetter = new Typesetter();
        $typesetter->listen(Starting::class, $startingListener = $this->newTestableListener(Starting::class));
        $typesetter->listen(MarkdownInitialized::class, $startingListener = $this->newTestableListener(MarkdownInitialized::class));
        $typesetter->listen(PdfInitialized::class, $startingListener = $this->newTestableListener(PdfInitialized::class));
        $typesetter->listen(ThemeAdded::class, $startingListener = $this->newTestableListener(ThemeAdded::class));
        $typesetter->listen(CoverHtmlAdded::class, $startingListener = $this->newTestableListener(CoverHtmlAdded::class));
        $typesetter->listen(TocGenerated::class, $startingListener = $this->newTestableListener(TocGenerated::class));
        $typesetter->listen(FooterGenerated::class, $startingListener = $this->newTestableListener(FooterGenerated::class));
        $typesetter->listen(ContentGenerating::class, $startingListener = $this->newTestableListener(ContentGenerating::class));
        $typesetter->listen(PdfRendering::class, $startingListener = $this->newTestableListener(PdfRendering::class));
        $typesetter->listen(Finished::class, $startingListener = $this->newTestableListener(Finished::class));

        $result = $typesetter->generate($config);

        self::assertNotEmpty($result);

        self::assertEquals(2, $observerForParsed->times);
    }

    public function testImageCover(): void
    {
        $observerForParsed = new class extends \Typesetterio\Typesetter\Observers\Observer {
            public function __construct(public int $times = 0)
            {
            }

            public function chapterParsed(Chapter $chapter): void
            {
                $this->times++;
            }
        };

        $theme = __DIR__ . '/../data/theme';
        $content = __DIR__ . '/../data/content';
        $config = new Config([
            'theme' => $theme,
            'content' => $content,
            'title' => 'The title',
            'author' => 'The author',
            'observers' => [
                $observerForParsed,
            ],
        ]);

        $typesetter = new Typesetter();
        $typesetter->listen(Starting::class, $startingListener = $this->newTestableListener(Starting::class));
        $typesetter->listen(MarkdownInitialized::class, $startingListener = $this->newTestableListener(MarkdownInitialized::class));
        $typesetter->listen(PdfInitialized::class, $startingListener = $this->newTestableListener(PdfInitialized::class));
        $typesetter->listen(ThemeAdded::class, $startingListener = $this->newTestableListener(ThemeAdded::class));
        $typesetter->listen(CoverImageAdded::class, $startingListener = $this->newTestableListener(CoverImageAdded::class));
        $typesetter->listen(TocGenerated::class, $startingListener = $this->newTestableListener(TocGenerated::class));
        $typesetter->listen(FooterGenerated::class, $startingListener = $this->newTestableListener(FooterGenerated::class));
        $typesetter->listen(ContentGenerating::class, $startingListener = $this->newTestableListener(ContentGenerating::class));
        $typesetter->listen(PdfRendering::class, $startingListener = $this->newTestableListener(PdfRendering::class));
        $typesetter->listen(Finished::class, $startingListener = $this->newTestableListener(Finished::class));

        $result = $typesetter->generate($config);

        self::assertNotEmpty($result);

        self::assertEquals(2, $observerForParsed->times);
    }

    protected function newTestableListener(string $dispatchedEventClass): callable
    {
        return function (Event $event) use ($dispatchedEventClass) {
            self::assertEquals($dispatchedEventClass, $event::class);
        };
    }
}
