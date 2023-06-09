<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Observers;

use DOMElement;
use Typesetterio\Typesetter\Contracts\Chapter;

class FirstElementInChapterCSSClass extends Observer
{
    public function __construct(protected string $class = 'chapter-beginning', protected bool $skipFirst = true)
    {
    }

    public function parsed(Chapter $chapter): void
    {
        if ($this->skipFirst === false || !$chapter->isFirstChapter()) {
            $dom = $this->getDomDocument($chapter);

            /** @var DOMElement $firstElement */
            $firstElement = $dom->firstChild;

            $classes = array_filter(explode(' ', $firstElement->getAttribute('class')));
            $classes[] = $this->class;
            $firstElement->setAttribute('class', implode(' ', array_unique($classes)));

            $chapter->setHtml($dom->saveHTML());
        }
    }
}
