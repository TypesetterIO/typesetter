<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Observers;

use Typesetterio\Typesetter\Contracts\Chapter;

class FirstElementInChapterCssClass extends Observer
{
    public function __construct(protected string $class = 'chapter-beginning', protected bool $skipFirst = true) {}

    public function chapterParsed(Chapter $chapter): void
    {
        if ($this->skipFirst === false || !$chapter->isFirstChapter()) {
            $document = $this->getHtmlDocument($chapter);

            $firstElement = $document->body->firstElementChild; // @phpstan-ignore property.nonObject (createFromString() always sets body)
            if ($firstElement === null) {
                return;
            }

            $firstElement->classList->add($this->class);

            $this->setHtmlFromDocument($chapter, $document);
        }
    }
}
