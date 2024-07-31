<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter;

use FilesystemIterator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Mpdf\Mpdf;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Typesetterio\Typesetter\Contracts\Event;
use Typesetterio\Typesetter\Exceptions\ListenerInvalidException;

class Typesetter
{
    protected array $listeners = [];

    public function listen(string $eventClass, callable $listener): void
    {
        $implementations = class_implements($eventClass);
        if ($implementations === false) {
            throw new ListenerInvalidException($eventClass . ' has failed to generate an implementation array.');
        }
        if (!in_array(Event::class, $implementations, true)) {
            throw new ListenerInvalidException($eventClass . ' does not implement the contract ' . Event::class);
        }

        $this->listeners[$eventClass][] = $listener;
    }

    public function generate(Config $bookConfig): string
    {
        $this->dispatch(new Events\Starting());

        $converter = new GithubFlavoredMarkdownConverter();

        $bookConfig->observers->initializedMarkdownEnvironment($converter->getEnvironment());

        $this->dispatch(new Events\InitializedMarkdown());

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'margin_left' => 27,
            'margin_right' => 27,
            'margin_bottom' => 14,
            'margin_top' => 14,
        ]);
        $mpdf->SetTitle($bookConfig->title);
        $mpdf->SetAuthor($bookConfig->author);

        $bookConfig->observers->initializedPdf($mpdf);

        $this->dispatch(new Events\PDFInitialized());

        $stylesheet = $bookConfig->theme . '/theme.html';
        $mpdf->WriteHTML(file_get_contents($stylesheet));
        $this->dispatch(new Events\ThemeAdded());

        if (is_readable($bookConfig->theme . '/cover.jpg')) {
            $mpdf->Image($bookConfig->theme . '/cover.jpg', 0, 0, 210, 297, 'jpg', '', true, false);
            $this->dispatch(new Events\CoverImageAdded());
        } elseif (is_readable($bookConfig->theme . '/cover.html')) {
            $mpdf->WriteHTML(file_get_contents($bookConfig->theme . '/cover.html'));
            $this->dispatch(new Events\CoverHtmlAdded());
        } else {
            $coverHtml = '<section style="text-align: center; page-break-after:always; padding-top: 100pt"><h1>%s</h1><h2>%s</h2></section>';
            $mpdf->WriteHTML(sprintf($coverHtml, $bookConfig->title, $bookConfig->author));
            $this->dispatch(new Events\CoverGenerated());
        }

        $bookConfig->observers->coverAdded($mpdf);

        if ($bookConfig->tocEnabled) {
            $tocLevels = ['H1' => 0, 'H2' => 1];
            $mpdf->h2toc = $tocLevels;
            $mpdf->h2bookmarks = $tocLevels;

            $mpdfTocDefinition = '<tocpagebreak toc-bookmarkText="Contents" toc-page-selector="toc-page"';
            $mpdfTocDefinition .= sprintf(' links="%s"', $bookConfig->tocLinks ? 'on' : 'off');
            if ($tocHeader = $bookConfig->tocHeader) {
                $mpdfTocDefinition .= sprintf(' toc-preHTML="%s"', htmlentities('<h1 id="toc-header">' . $tocHeader . '</h1>'));
            }
            $mpdfTocDefinition .= '>';
            $mpdf->WriteHTML($mpdfTocDefinition);
            $this->dispatch(new Events\TOCGenerated());
        }

        if ($bookConfig->footer) {
            $footerDefinition = '<footer class="footer">' . htmlentities($bookConfig->footer) . '</footer>';
            $mpdf->SetHTMLFooter($footerDefinition);
            $this->dispatch(new Events\FooterGenerated());
        }

        $this->dispatch(new Events\ContentGenerating());

        $contentFiles = (new Collection(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($bookConfig->content, FilesystemIterator::KEY_AS_FILENAME | FilesystemIterator::SKIP_DOTS))))
            ->filter(fn ($file) => in_array($file->getExtension(), $bookConfig->markdownExtensions, true))
            ->filter($bookConfig->contentFilter)
            ->unless(
                empty($bookConfig->contentExtra),
                fn(Collection $collection) => $collection->merge(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($bookConfig->contentExtra, FilesystemIterator::KEY_AS_FILENAME | FilesystemIterator::SKIP_DOTS)))
            )
            ->sortKeys();

        $totalChapters = $contentFiles->count();
        $chapterNumber = 0;
        foreach ($contentFiles as $contentFile) {
            $chapterNumber++;

            $markdown = file_get_contents($contentFile->getPathname());
            $chapter = new Chapter(
                markdown: $converter->convert($markdown),
                chapterNumber: $chapterNumber,
                totalChapters: $totalChapters
            );

            $bookConfig->observers->parsed($chapter);

            $mpdf->WriteHTML($chapter->getHtml());
        }

        $this->dispatch(new Events\PDFRendering());

        try {
            return $mpdf->OutputBinaryData();
        } finally {
            $this->dispatch(new Events\Finished());
        }
    }

    protected function dispatch(Event $event): void
    {
        foreach (Arr::get($this->listeners, get_class($event), []) as $listener) {
            $listener($event);
        }
    }
}
