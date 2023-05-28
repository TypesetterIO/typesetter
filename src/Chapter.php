<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter;

class Chapter implements Contracts\Chapter
{
    protected string $html;

    public function __construct(protected string $markdown, protected int $chapterNumber, protected int $totalChapters)
    {
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function setHtml($html): void
    {
        $this->html = (string) $html;
    }

    public function getChapterNumber(): int
    {
        return $this->chapterNumber;
    }

    public function getTotalChapters(): int
    {
        return $this->totalChapters;
    }

    public function isFirstChapter(): bool
    {
        return $this->chapterNumber === 1;
    }

    public function isLastChapter(): bool
    {
        return $this->chapterNumber === $this->totalChapters;
    }
}
