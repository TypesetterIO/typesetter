<?php

declare(strict_types=1);

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use Mpdf\Mpdf;
use Typesetterio\Typesetter\Contracts\Chapter;
use Typesetterio\Typesetter\Observers\Observer;

class DemoObserver extends Observer
{
    public function markdownEnvironmentInitialized(Environment $environment): void
    {
        $environment->addExtension(new FootnoteExtension());
    }

    public function pdfInitialized(Mpdf $mpdf): void
    {
        $mpdf->SetSubject('Most Typesetter features in one PDF');
        $mpdf->SetKeywords('typesetter, markdown, pdf, demo');

        $mpdf->SetWatermarkText('DEMO', 0.04);
        $mpdf->showWatermarkText = true;
    }

    public function coverAdded(Mpdf $mpdf): void
    {
        $mpdf->WriteHTML(
            '<div class="copyright-page">'
            . '<p><strong>Typesetter Demo Book</strong></p>'
            . '<p>This page is not in the content directory. <code>DemoObserver::coverAdded()</code> '
            . 'wrote it straight to mPDF, between the cover and the table of contents.</p>'
            . '<p>The same observer set this PDF\'s subject and keywords and the faint DEMO watermark '
            . 'in <code>pdfInitialized()</code>.</p>'
            . '</div>',
        );
    }

    public function chapterParsed(Chapter $chapter): void
    {
        $dom = $this->getDomDocument($chapter);
        $heading = $dom->getElementsByTagName('h1')->item(0);

        $label = $dom->createElement('p');
        $label->textContent = sprintf('Chapter %d of %d', $chapter->getChapterNumber(), $chapter->getTotalChapters());
        $label->setAttribute('class', 'chapter-label');
        $dom->insertBefore($label, $heading);

        $subtitle = $dom->createElement('p');
        $subtitle->textContent = $chapter->getMetaData()['subtitle']; // @phpstan-ignore assign.propertyType
        $subtitle->setAttribute('class', 'chapter-subtitle');
        $dom->insertBefore($subtitle, $heading->nextSibling); // @phpstan-ignore property.nonObject

        $chapter->setHtml($dom->saveHTML()); // @phpstan-ignore argument.type
    }
}
