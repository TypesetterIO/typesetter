<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Contracts;

interface Chapter
{
    public function getHtml(): string;

    public function setHtml($html): void;

    public function getChapterNumber(): int;

    public function getTotalChapters(): int;

    public function isFirstChapter(): bool;

    public function isLastChapter(): bool;

    public function getMetaData(): array;
}
