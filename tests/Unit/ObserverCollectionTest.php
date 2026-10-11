<?php

declare(strict_types=1);

namespace Tests\Unit;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Output\RenderedContent;
use Mockery;
use Mpdf\Mpdf;
use Tests\TestCase;
use Typesetterio\Typesetter\Chapter;
use Typesetterio\Typesetter\Exceptions\InvalidConfigException;
use Typesetterio\Typesetter\ObserverCollection;
use Typesetterio\Typesetter\Observers\Observer;

class ObserverCollectionTest extends TestCase
{
    public function testMarkdownEnvironmentInitialized(): void
    {
        $environment = new Environment();
        $observers = [Mockery::mock(Observer::class), Mockery::mock(Observer::class), Mockery::mock(Observer::class)];
        foreach ($observers as $observer) {
            $observer->expects('markdownEnvironmentInitialized')->with($environment);
        }

        (new ObserverCollection($observers))->markdownEnvironmentInitialized($environment);
    }

    public function testChapterParsed(): void
    {
        $chapter = new Chapter(new RenderedContent(new Document(), ''), 1, 1);
        $observers = [Mockery::mock(Observer::class), Mockery::mock(Observer::class), Mockery::mock(Observer::class)];
        foreach ($observers as $observer) {
            $observer->expects('chapterParsed')->with($chapter);
        }

        (new ObserverCollection($observers))->chapterParsed($chapter);
    }

    public function testPdfInitialized(): void
    {
        $mpdf = Mockery::mock(Mpdf::class);
        $observers = [Mockery::mock(Observer::class), Mockery::mock(Observer::class), Mockery::mock(Observer::class)];
        foreach ($observers as $observer) {
            $observer->expects('pdfInitialized')->with($mpdf);
        }

        (new ObserverCollection($observers))->pdfInitialized($mpdf);
    }

    public function testCoverAdded(): void
    {
        $mpdf = Mockery::mock(Mpdf::class);
        $observers = [Mockery::mock(Observer::class), Mockery::mock(Observer::class), Mockery::mock(Observer::class)];
        foreach ($observers as $observer) {
            $observer->expects('coverAdded')->with($mpdf);
        }

        (new ObserverCollection($observers))->coverAdded($mpdf);
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
