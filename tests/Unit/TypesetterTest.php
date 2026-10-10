<?php

declare(strict_types=1);

namespace Tests\Unit;

use Closure;
use League\CommonMark\Environment\Environment;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\MpdfImageException;
use RuntimeException;
use SplFileInfo;
use Tests\TestCase;
use Typesetterio\Typesetter\Config;
use Typesetterio\Typesetter\Contracts\Chapter;
use Typesetterio\Typesetter\Contracts\Event;
use Typesetterio\Typesetter\Events\ContentGenerating;
use Typesetterio\Typesetter\Events\CoverGenerated;
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
use Typesetterio\Typesetter\Observers\Observer;
use Typesetterio\Typesetter\Typesetter;

class TypesetterTest extends TestCase
{
    public function testRegisterListenerNoImplementationsThrowsException(): void
    {
        $this->expectException(InvalidListenerException::class);
        $this->expectExceptionMessage('not-a-class does not implement Typesetterio\Typesetter\Contracts\Event');

        (new Typesetter())->listen('not-a-class', function () {});
    }

    public function testRegisterListenersNotImplementEventContractThrowsException(): void
    {
        $this->expectException(InvalidListenerException::class);
        $this->expectExceptionMessage('Typesetterio\Typesetter\Observers\Observer does not implement Typesetterio\Typesetter\Contracts\Event');

        (new Typesetter())->listen(Observer::class, function () {});
    }

    public function testRegisterListenerForEventContractItselfThrowsException(): void
    {
        $this->expectException(InvalidListenerException::class);
        $this->expectExceptionMessage('Typesetterio\Typesetter\Contracts\Event does not implement Typesetterio\Typesetter\Contracts\Event');

        (new Typesetter())->listen(Event::class, function () {});
    }

    public function testEventsAndObserverHooksFireInOrder(): void
    {
        $log = [];
        $result = $this->newRecordingTypesetter($log)->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'observers' => [$this->newRecordingObserver($log)],
        ]));

        self::assertStringStartsWith('%PDF-', $result);
        self::assertSame([
            Starting::class,
            'observer:markdownEnvironmentInitialized',
            MarkdownInitialized::class,
            'observer:pdfInitialized',
            PdfInitialized::class,
            ThemeAdded::class,
            CoverImageAdded::class,
            'observer:coverAdded',
            TocGenerated::class,
            FooterGenerated::class,
            ContentGenerating::class,
            'observer:chapterParsed 1/2 first=1 last=0 <h1>One</h1>',
            'observer:chapterParsed 2/2 first=0 last=1 <h1>Two</h1>',
            PdfRendering::class,
            Finished::class,
        ], $log);
    }

    public function testHtmlCover(): void
    {
        $log = [];
        $this->newRecordingTypesetter($log)->generate(new Config([
            'theme' => __DIR__ . '/../data/another-theme',
            'content' => __DIR__ . '/../data/content',
        ]));

        self::assertContains(CoverHtmlAdded::class, $log);
        self::assertNotContains(CoverImageAdded::class, $log);
    }

    public function testGeneratedCover(): void
    {
        $log = [];
        $this->newRecordingTypesetter($log)->generate(new Config([
            'theme' => __DIR__ . '/../data/bare-theme',
            'content' => __DIR__ . '/../data/content',
        ]));

        self::assertContains(CoverGenerated::class, $log);
    }

    public function testTocAndFooterCanBeDisabled(): void
    {
        $log = [];
        $this->newRecordingTypesetter($log)->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'toc-enabled' => false,
            'footer' => '',
        ]));

        self::assertNotContains(TocGenerated::class, $log);
        self::assertNotContains(FooterGenerated::class, $log);
    }

    public function testTocWithoutHeaderOrLinks(): void
    {
        $result = (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'toc-header' => '',
            'toc-links' => false,
        ]));

        self::assertStringStartsWith('%PDF-', $result);
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

    public function testListenersFireInRegistrationOrder(): void
    {
        $log = [];
        $typesetter = new Typesetter();
        $typesetter->listen(Starting::class, function () use (&$log) {
            $log[] = 'first';
        });
        $typesetter->listen(Starting::class, function () use (&$log) {
            $log[] = 'second';
        });

        $typesetter->generate(new Config([
            'theme' => __DIR__ . '/../data/bare-theme',
            'content' => __DIR__ . '/../data/content',
        ]));

        self::assertSame(['first', 'second'], $log);
    }

    public function testContentFilter(): void
    {
        $log = [];
        $seen = [];
        (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'content-filter' => function (SplFileInfo $file, string $filename) use (&$seen): bool {
                $seen[] = $filename;
                self::assertSame($filename, $file->getFilename());

                return $filename !== '02.md';
            },
            'observers' => [$this->newRecordingObserver($log)],
        ]));

        sort($seen);
        self::assertSame(['01.md', '02.md'], $seen);
        self::assertSame([
            'observer:markdownEnvironmentInitialized',
            'observer:pdfInitialized',
            'observer:coverAdded',
            'observer:chapterParsed 1/1 first=1 last=1 <h1>One</h1>',
        ], $log);
    }

    public function testContentExtra(): void
    {
        $log = [];
        (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'content-extra' => __DIR__ . '/../data/content-extra',
            'observers' => [$this->newRecordingObserver($log)],
        ]));

        self::assertSame([
            'observer:markdownEnvironmentInitialized',
            'observer:pdfInitialized',
            'observer:coverAdded',
            'observer:chapterParsed 1/3 first=1 last=0 <h1>Preface</h1>',
            'observer:chapterParsed 2/3 first=0 last=0 <h1>One</h1>',
            'observer:chapterParsed 3/3 first=0 last=1 <h1>Two</h1>',
        ], $log);
    }

    public function testMarkdownExtensions(): void
    {
        $log = [];
        (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'markdown-extensions' => ['txt'],
            'observers' => [$this->newRecordingObserver($log)],
        ]));

        self::assertSame([
            'observer:markdownEnvironmentInitialized',
            'observer:pdfInitialized',
            'observer:coverAdded',
            'observer:chapterParsed 1/1 first=1 last=1 <p>Notes</p>',
        ], $log);
    }

    public function testDuplicateContentFileNamesThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Duplicate content file names: 01.md');

        (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content-duplicate',
        ]));
    }

    public function testDuplicateContentExtraFileNameThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Duplicate content file names: 01.md');

        (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'content-extra' => __DIR__ . '/../data/content-extra-duplicate',
        ]));
    }

    public function testNoContentFilesThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('No content files found in ' . __DIR__ . '/../data/theme');

        (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/theme',
        ]));
    }

    public function testUnreadableContentFileThrowsException(): void
    {
        $directory = sys_get_temp_dir() . '/typesetter-' . uniqid();
        $file = $directory . '/01.md';
        mkdir($directory);
        copy(__DIR__ . '/../data/content/01.md', $file);
        chmod($file, 0);

        try {
            if (is_readable($file)) {
                self::markTestSkipped('chmod 000 does not make the file unreadable for this user');
            }

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage($file);

            (new Typesetter())->generate(new Config([
                'theme' => __DIR__ . '/../data/theme',
                'content' => $directory,
            ]));
        } finally {
            chmod($file, 0644);
            unlink($file);
            rmdir($directory);
        }
    }

    public function testBrokenImageThrowsException(): void
    {
        $this->expectException(MpdfImageException::class);

        (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content-broken-image',
        ]));
    }

    public function testFinishedDoesNotFireWhenOutputFails(): void
    {
        $log = [];
        $typesetter = $this->newRecordingTypesetter($log);
        $config = new Config([
            'theme' => __DIR__ . '/../data/bare-theme',
            'content' => __DIR__ . '/../data/content',
            'observers' => [
                new class extends Observer {
                    public function coverAdded(Mpdf $mpdf): void
                    {
                        $mpdf->PDFA = true;
                        $mpdf->SetProtection(['print']);
                    }
                },
            ],
        ]);

        try {
            $typesetter->generate($config);
            self::fail('Expected ' . MpdfException::class);
        } catch (MpdfException $e) {
            self::assertSame('PDF/A1-b or PDF/X1-a does not permit encryption of documents.', $e->getMessage());
        }

        self::assertSame(PdfRendering::class, end($log));
    }

    public function testTitleAuthorAndTocHeaderAreEscaped(): void
    {
        $result = (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/bare-theme',
            'content' => __DIR__ . '/../data/content',
            'title' => 'Tom & <i>Jerry</i>',
            'author' => 'A <b>B</b>',
            'toc-header' => 'Contents & <i>more</i>',
            'observers' => [
                new class extends Observer {
                    public function pdfInitialized(Mpdf $mpdf): void
                    {
                        $mpdf->SetCompression(false);
                        $mpdf->WriteHTML('<style>body, h1, h2 { font-family: chelvetica }</style>');
                    }
                },
            ],
        ]));

        self::assertStringContainsString('(Tom & <i>Jerry</i>)', $result);
        self::assertStringContainsString('(A <b>B</b>)', $result);
        self::assertStringContainsString('(Contents & <i>more</i>)', $result);
    }

    public function testFooterPrintsOnPagesUnderBarePageRule(): void
    {
        $result = (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/bare-theme',
            'content' => __DIR__ . '/../data/content',
            'footer' => 'Foot {PAGENO}',
            'observers' => [
                new class extends Observer {
                    public function pdfInitialized(Mpdf $mpdf): void
                    {
                        $mpdf->SetCompression(false);
                        $mpdf->WriteHTML('<style>@page { margin: 20mm } @page toc-page { margin: 30mm } footer { font-family: chelvetica }</style>');
                    }

                    public function chapterParsed(Chapter $chapter): void
                    {
                        if (! $chapter->isFirstChapter()) {
                            $chapter->setHtml('<pagebreak>' . $chapter->getHtml());
                        }
                    }
                },
            ],
        ]));

        self::assertStringNotContainsString('(Foot 2)', $result);
        self::assertStringContainsString('(Foot 3)', $result);
        self::assertStringContainsString('(Foot 4)', $result);
    }

    public function testFooterPrintsOnNamedPages(): void
    {
        $result = (new Typesetter())->generate(new Config([
            'theme' => __DIR__ . '/../data/bare-theme',
            'content' => __DIR__ . '/../data/content',
            'footer' => 'Foot {PAGENO}',
            'observers' => [
                new class extends Observer {
                    public function pdfInitialized(Mpdf $mpdf): void
                    {
                        $mpdf->SetCompression(false);
                        $mpdf->WriteHTML('<style>@page wide { margin: 10mm } .wide { page: wide } footer { font-family: chelvetica }</style>');
                    }

                    public function chapterParsed(Chapter $chapter): void
                    {
                        if ($chapter->isLastChapter()) {
                            $chapter->setHtml('<div class="wide">' . $chapter->getHtml() . '</div>');
                        }
                    }
                },
            ],
        ]));

        self::assertStringContainsString('(Foot 3)', $result);
        self::assertStringContainsString('(Foot 4)', $result);
    }

    /** @param list<string> $log */
    private function newRecordingTypesetter(array &$log): Typesetter
    {
        $typesetter = new Typesetter();
        $listener = function (Event $event) use (&$log): void {
            $log[] = $event::class;
        };
        $events = [
            Starting::class,
            MarkdownInitialized::class,
            PdfInitialized::class,
            ThemeAdded::class,
            CoverImageAdded::class,
            CoverHtmlAdded::class,
            CoverGenerated::class,
            TocGenerated::class,
            FooterGenerated::class,
            ContentGenerating::class,
            PdfRendering::class,
            Finished::class,
        ];
        foreach ($events as $eventClass) {
            $typesetter->listen($eventClass, $listener);
        }

        return $typesetter;
    }

    /** @param list<string> $log */
    private function newRecordingObserver(array &$log): Observer
    {
        $record = function (string $entry) use (&$log): void {
            $log[] = $entry;
        };

        return new class ($record) extends Observer {
            public function __construct(private readonly Closure $record) {}

            public function markdownEnvironmentInitialized(Environment $environment): void
            {
                ($this->record)('observer:markdownEnvironmentInitialized');
            }

            public function pdfInitialized(Mpdf $mpdf): void
            {
                ($this->record)('observer:pdfInitialized');
            }

            public function coverAdded(Mpdf $mpdf): void
            {
                ($this->record)('observer:coverAdded');
            }

            public function chapterParsed(Chapter $chapter): void
            {
                ($this->record)(sprintf(
                    'observer:chapterParsed %d/%d first=%d last=%d %s',
                    $chapter->getChapterNumber(),
                    $chapter->getTotalChapters(),
                    $chapter->isFirstChapter(),
                    $chapter->isLastChapter(),
                    trim($chapter->getHtml()),
                ));
            }
        };
    }
}
