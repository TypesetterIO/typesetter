<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Observers;

use Typesetterio\Typesetter\Contracts\Chapter;

class Credits extends Observer
{
    public function __construct(protected string $class = 'credits-box')
    {
    }

    public function parsed(Chapter $chapter): void
    {
        if ($chapter->isLastChapter()) {
            $creditsHtml = sprintf(
                '<div class="%s">Created using <a href="https:://typesetter.io">Typesetter.io</a></div>',
                $this->class,
            );

            $chapter->setHtml($chapter->getHtml() . $creditsHtml);
        }
    }
}
