<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Contracts;

use Stringable;

interface Chapter
{
    public function getHtml(): string;

    public function setHtml(string|Stringable $html): void;

    public function getChapterNumber(): int;

    public function getTotalChapters(): int;

    public function isFirstChapter(): bool;

    public function isLastChapter(): bool;

    /** @return array<string, mixed> */
    public function getMetaData(): array;
}
