<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Typesetterio\Typesetter\Config;
use Typesetterio\Typesetter\Contracts\Chapter;
use Typesetterio\Typesetter\Contracts\Event;
use Typesetterio\Typesetter\Contracts\Observer;
use Typesetterio\Typesetter\Events\ContentGenerating;
use Typesetterio\Typesetter\Events\CoverGenerated;
use Typesetterio\Typesetter\Events\CoverHtmlAdded;
use Typesetterio\Typesetter\Events\CoverImageAdded;
use Typesetterio\Typesetter\Events\Finished;
use Typesetterio\Typesetter\Events\FooterGenerated;
use Typesetterio\Typesetter\Events\InitializedMarkdown;
use Typesetterio\Typesetter\Events\PDFInitialized;
use Typesetterio\Typesetter\Events\PDFRendering;
use Typesetterio\Typesetter\Events\Starting;
use Typesetterio\Typesetter\Events\ThemeAdded;
use Typesetterio\Typesetter\Events\TOCGenerated;
use Typesetterio\Typesetter\Exceptions\ListenerInvalidException;
use Typesetterio\Typesetter\Typesetter;

class TypesetterTest extends TestCase
{
    // this test generates a warning - but that's ok
    public function testRegisterListenerNoImplementationsThrowsException(): void
    {
        $this->expectException(ListenerInvalidException::class);
        $this->expectExceptionMessage('not-a-class has failed to generate an implementation array.');

        $typesetter = new Typesetter();
        $typesetter->listen('not-a-class', function () {
        });
    }

    public function testRegisterListenersNotImplementEventContractThrowsException(): void
    {
        $this->expectException(ListenerInvalidException::class);
        $this->expectExceptionMessage('Typesetterio\Typesetter\Contracts\Observer does not implement the contract Typesetterio\Typesetter\Contracts\Event');

        $typesetter = new Typesetter();
        $typesetter->listen(Observer::class, function () {
        });
    }

    public function testGeneratedCoverNoTocNoFooterNoContentFiles(): void
    {
        $theme = __DIR__ . '/../data/theme';
        $config = new Config([
            'theme' => $theme,
            'title' => 'A title',
            'author' => 'An author',
        ]);

        $typesetter = new Typesetter();
        $typesetter->listen(Starting::class, $startingListener = $this->newTestableListener(Starting::class));
        $typesetter->listen(InitializedMarkdown::class, $startingListener = $this->newTestableListener(InitializedMarkdown::class));
        $typesetter->listen(PDFInitialized::class, $startingListener = $this->newTestableListener(PDFInitialized::class));
        $typesetter->listen(ThemeAdded::class, $startingListener = $this->newTestableListener(ThemeAdded::class));
        $typesetter->listen(CoverGenerated::class, $startingListener = $this->newTestableListener(CoverGenerated::class));
        $typesetter->listen(ContentGenerating::class, $startingListener = $this->newTestableListener(ContentGenerating::class));
        $typesetter->listen(PDFRendering::class, $startingListener = $this->newTestableListener(PDFRendering::class));
        $typesetter->listen(Finished::class, $startingListener = $this->newTestableListener(Finished::class));

        $result = $typesetter->generate($config);

        self::assertNotEmpty($result); // the best I can try without really digging in
    }

    public function testHtmlCoverWithTocNoTocHeaderWithFooterWithFiles(): void
    {
        $observerForParsed = new class extends \Typesetterio\Typesetter\Observers\Observer {
            public function __construct(public int $times = 0)
            {
            }

            public function parsed(Chapter $chapter): void
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
        $typesetter->listen(InitializedMarkdown::class, $startingListener = $this->newTestableListener(InitializedMarkdown::class));
        $typesetter->listen(PDFInitialized::class, $startingListener = $this->newTestableListener(PDFInitialized::class));
        $typesetter->listen(ThemeAdded::class, $startingListener = $this->newTestableListener(ThemeAdded::class));
        $typesetter->listen(CoverHtmlAdded::class, $startingListener = $this->newTestableListener(CoverHtmlAdded::class));
        $typesetter->listen(TOCGenerated::class, $startingListener = $this->newTestableListener(TOCGenerated::class));
        $typesetter->listen(FooterGenerated::class, $startingListener = $this->newTestableListener(FooterGenerated::class));
        $typesetter->listen(ContentGenerating::class, $startingListener = $this->newTestableListener(ContentGenerating::class));
        $typesetter->listen(PDFRendering::class, $startingListener = $this->newTestableListener(PDFRendering::class));
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

            public function parsed(Chapter $chapter): void
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
        $typesetter->listen(InitializedMarkdown::class, $startingListener = $this->newTestableListener(InitializedMarkdown::class));
        $typesetter->listen(PDFInitialized::class, $startingListener = $this->newTestableListener(PDFInitialized::class));
        $typesetter->listen(ThemeAdded::class, $startingListener = $this->newTestableListener(ThemeAdded::class));
        $typesetter->listen(CoverImageAdded::class, $startingListener = $this->newTestableListener(CoverImageAdded::class));
        $typesetter->listen(TOCGenerated::class, $startingListener = $this->newTestableListener(TOCGenerated::class));
        $typesetter->listen(FooterGenerated::class, $startingListener = $this->newTestableListener(FooterGenerated::class));
        $typesetter->listen(ContentGenerating::class, $startingListener = $this->newTestableListener(ContentGenerating::class));
        $typesetter->listen(PDFRendering::class, $startingListener = $this->newTestableListener(PDFRendering::class));
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
