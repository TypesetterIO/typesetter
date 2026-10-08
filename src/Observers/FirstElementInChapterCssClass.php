<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Observers;

use DOMElement;
use Typesetterio\Typesetter\Contracts\Chapter;

class FirstElementInChapterCssClass extends Observer
{
    public function __construct(protected string $class = 'chapter-beginning', protected bool $skipFirst = true)
    {
    }

    public function chapterParsed(Chapter $chapter): void
    {
        if ($this->skipFirst === false || !$chapter->isFirstChapter()) {
            $dom = $this->getDomDocument($chapter);

            $firstElement = $dom->firstChild;
            if (!$firstElement instanceof DOMElement) {
                return;
            }

            $classes = array_filter(explode(' ', $firstElement->getAttribute('class')));
            $classes[] = $this->class;
            $firstElement->setAttribute('class', implode(' ', array_unique($classes)));

            $chapter->setHtml($dom->saveHTML());
        }
    }
}
