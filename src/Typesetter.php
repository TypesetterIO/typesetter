<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter;

use FilesystemIterator;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Mpdf\Mpdf;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Typesetterio\Typesetter\Contracts\Event;
use Typesetterio\Typesetter\Exceptions\InvalidConfigException;
use Typesetterio\Typesetter\Exceptions\InvalidListenerException;

class Typesetter
{
    /** @var array<class-string<Event>, list<callable(Event): void>> */
    protected array $listeners = [];

    public function listen(string $eventClass, callable $listener): void
    {
        if (!is_subclass_of($eventClass, Event::class)) {
            throw new InvalidListenerException(sprintf('%s does not implement %s', $eventClass, Event::class));
        }

        $this->listeners[$eventClass][] = $listener;
    }

    public function generate(Config $bookConfig): string
    {
        $this->dispatch(new Events\Starting());

        $converter = new GithubFlavoredMarkdownConverter();

        $bookConfig->observers->markdownEnvironmentInitialized($converter->getEnvironment());

        $this->dispatch(new Events\MarkdownInitialized());

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'margin_left' => 27,
            'margin_right' => 27,
            'margin_bottom' => 14,
            'margin_top' => 14,
            'showImageErrors' => true,
        ]);
        $mpdf->SetTitle($bookConfig->title);
        $mpdf->SetAuthor($bookConfig->author);

        $bookConfig->observers->pdfInitialized($mpdf);

        $this->dispatch(new Events\PdfInitialized());

        $stylesheet = $bookConfig->theme . '/theme.html';
        $themeHtml = file_get_contents($stylesheet);
        if ($themeHtml === false) {
            throw new RuntimeException('Unable to read ' . $stylesheet);
        }
        $mpdf->WriteHTML($themeHtml);
        $this->dispatch(new Events\ThemeAdded());

        if (is_readable($bookConfig->theme . '/cover.jpg')) {
            $mpdf->Image($bookConfig->theme . '/cover.jpg', 0, 0, 210, 297, 'jpg', '', true, false);
            $this->dispatch(new Events\CoverImageAdded());
        } elseif (is_readable($bookConfig->theme . '/cover.html')) {
            $coverHtml = file_get_contents($bookConfig->theme . '/cover.html');
            if ($coverHtml === false) {
                throw new RuntimeException('Unable to read ' . $bookConfig->theme . '/cover.html');
            }
            $mpdf->WriteHTML($coverHtml);
            $this->dispatch(new Events\CoverHtmlAdded());
        } else {
            $coverHtml = '<section style="text-align: center; page-break-after:always; padding-top: 100pt"><h1>%s</h1><h2>%s</h2></section>';
            $mpdf->WriteHTML(sprintf($coverHtml, htmlspecialchars($bookConfig->title), htmlspecialchars($bookConfig->author)));
            $this->dispatch(new Events\CoverGenerated());
        }

        $bookConfig->observers->coverAdded($mpdf);

        $mpdf->h2bookmarks = $bookConfig->tocLevels;

        if ($bookConfig->tocEnabled) {
            $mpdf->h2toc = $bookConfig->tocLevels;

            $tocBookmarkText = htmlspecialchars($bookConfig->tocHeader ?: 'Contents');
            $mpdfTocDefinition = sprintf('<tocpagebreak toc-bookmarkText="%s" toc-page-selector="toc-page"', $tocBookmarkText);
            $mpdfTocDefinition .= sprintf(' links="%s"', $bookConfig->tocLinks ? 'on' : 'off');
            if ($tocHeader = $bookConfig->tocHeader) {
                $mpdfTocDefinition .= sprintf(' toc-preHTML="%s"', htmlentities('<h1 id="toc-header">' . htmlspecialchars($tocHeader) . '</h1>'));
            }
            $mpdfTocDefinition .= '>';
            $mpdf->WriteHTML($mpdfTocDefinition);
            $this->dispatch(new Events\TocGenerated());
        }

        if ($bookConfig->footer) {
            $footerDefinition = '<footer class="footer">' . htmlentities($bookConfig->footer) . '</footer>';
            $mpdf->SetHTMLFooter($footerDefinition);
            $this->dispatch(new Events\FooterGenerated());
        }

        $this->dispatch(new Events\ContentGenerating());

        /** @var list<SplFileInfo> $contentFiles */
        $contentFiles = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            $bookConfig->content,
            FilesystemIterator::SKIP_DOTS,
        )), false);
        $hasMarkdownExtension = fn(SplFileInfo $file) => $file->isFile()
            && in_array($file->getExtension(), $bookConfig->markdownExtensions, true);
        $contentFiles = array_values(array_filter(
            $contentFiles,
            fn(SplFileInfo $file) => $hasMarkdownExtension($file) && ($bookConfig->contentFilter)($file, $file->getFilename()),
        ));
        if ($bookConfig->contentExtra !== '') {
            /** @var list<SplFileInfo> $extraFiles */
            $extraFiles = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
                $bookConfig->contentExtra,
                FilesystemIterator::SKIP_DOTS,
            )), false);
            $contentFiles = array_merge($contentFiles, array_values(array_filter($extraFiles, $hasMarkdownExtension)));
        }
        if ($contentFiles === []) {
            throw new InvalidConfigException('No content files found in ' . $bookConfig->content);
        }

        $fileNameCounts = array_count_values(array_map(fn(SplFileInfo $file) => $file->getFilename(), $contentFiles));
        $duplicates = array_keys(array_filter($fileNameCounts, fn(int $count) => $count > 1));
        if ($duplicates !== []) {
            throw new InvalidConfigException('Duplicate content file names: ' . implode(', ', $duplicates));
        }

        usort($contentFiles, fn(SplFileInfo $a, SplFileInfo $b) => strcmp($a->getFilename(), $b->getFilename()));

        $totalChapters = count($contentFiles);
        $chapterNumber = 0;
        foreach ($contentFiles as $contentFile) {
            $chapterNumber++;

            if (!$contentFile->isReadable()) {
                throw new RuntimeException('Unable to read ' . $contentFile->getPathname());
            }
            $markdown = file_get_contents($contentFile->getPathname());
            if ($markdown === false) {
                throw new RuntimeException('Unable to read ' . $contentFile->getPathname());
            }
            $chapter = new Chapter(
                markdown: $converter->convert($markdown),
                chapterNumber: $chapterNumber,
                totalChapters: $totalChapters,
            );

            $bookConfig->observers->chapterParsed($chapter);

            $mpdf->WriteHTML($chapter->getHtml());
        }

        $this->dispatch(new Events\PdfRendering());

        $pdf = $mpdf->OutputBinaryData();
        $this->dispatch(new Events\Finished());

        return $pdf;
    }

    protected function dispatch(Event $event): void
    {
        foreach ($this->listeners[get_class($event)] ?? [] as $listener) {
            $listener($event);
        }
    }
}
