<?php

declare(strict_types=1);

namespace Tests\Unit;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Output\RenderedContent;
use Mpdf\Mpdf;
use Tests\TestCase;
use Typesetterio\Typesetter\Chapter as ChapterConcrete;
use Typesetterio\Typesetter\Contracts\Chapter;
use Typesetterio\Typesetter\Exceptions\InvalidConfigException;
use Typesetterio\Typesetter\ObserverCollection;
use Typesetterio\Typesetter\Observers\Observer;

class ObserverCollectionTest extends TestCase
{
    public function testMarkdownEnvironmentInitialized(): void
    {
        $ob1 = new class extends Observer {
            public function __construct(public bool $loaded = false) {}

            public function markdownEnvironmentInitialized(Environment $environment): void
            {
                $this->loaded = true;
            }
        };

        $ob2 = new class extends Observer {
            public function __construct(public bool $loaded = false) {}

            public function markdownEnvironmentInitialized(Environment $environment): void
            {
                $this->loaded = true;
            }
        };

        $ob3 = new class extends Observer {
            public function __construct(public bool $loaded = false) {}

            public function markdownEnvironmentInitialized(Environment $environment): void
            {
                $this->loaded = true;
            }
        };

        $collection = new ObserverCollection([$ob1, $ob2, $ob3]);
        $collection->markdownEnvironmentInitialized(new Environment());
        self::assertTrue($ob1->loaded);
        self::assertTrue($ob2->loaded);
        self::assertTrue($ob3->loaded);
    }

    public function testChapterParsed(): void
    {
        $ob1 = new class extends Observer {
            public function __construct(public bool $loaded = false) {}

            public function chapterParsed(Chapter $chapter): void
            {
                $this->loaded = true;
            }
        };

        $ob2 = new class extends Observer {
            public function __construct(public bool $loaded = false) {}

            public function chapterParsed(Chapter $chapter): void
            {
                $this->loaded = true;
            }
        };

        $ob3 = new class extends Observer {
            public function __construct(public bool $loaded = false) {}

            public function chapterParsed(Chapter $chapter): void
            {
                $this->loaded = true;
            }
        };

        $collection = new ObserverCollection([$ob1, $ob2, $ob3]);
        $collection->chapterParsed(new ChapterConcrete($this->createStub(RenderedContent::class), 1, 1));
        self::assertTrue($ob1->loaded);
        self::assertTrue($ob2->loaded);
        self::assertTrue($ob3->loaded);
    }

    public function testPdfInitialized(): void
    {
        $ob1 = new class extends Observer {
            public function __construct(public bool $loaded = false) {}

            public function pdfInitialized(Mpdf $mpdf): void
            {
                $this->loaded = true;
            }
        };
        $ob2 = clone $ob1;
        $ob3 = clone $ob1;

        $collection = new ObserverCollection([$ob1, $ob2, $ob3]);
        $collection->pdfInitialized($this->createStub(Mpdf::class));
        self::assertTrue($ob1->loaded);
        self::assertTrue($ob2->loaded);
        self::assertTrue($ob3->loaded);
    }

    public function testCoverAdded(): void
    {
        $ob1 = new class extends Observer {
            public function __construct(public bool $loaded = false) {}

            public function coverAdded(Mpdf $mpdf): void
            {
                $this->loaded = true;
            }
        };
        $ob2 = clone $ob1;
        $ob3 = clone $ob1;

        $collection = new ObserverCollection([$ob1, $ob2, $ob3]);
        $collection->coverAdded($this->createStub(Mpdf::class));
        self::assertTrue($ob1->loaded);
        self::assertTrue($ob2->loaded);
        self::assertTrue($ob3->loaded);
    }

    public function testNonObserverThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageIs('observers[1] must be an instance of Typesetterio\Typesetter\Contracts\Observer, string given');

        new ObserverCollection([
            new class extends Observer {},
            'nope',
        ]);
    }
}
