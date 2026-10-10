<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter;

use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Output\RenderedContentInterface;
use Stringable;
use UnexpectedValueException;

final class Chapter implements Contracts\Chapter
{
    private string $html;

    public function __construct(private readonly RenderedContentInterface $markdown, private readonly int $chapterNumber, private readonly int $totalChapters)
    {
        $this->setHtml($markdown);
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function setHtml(string|Stringable $html): void
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

    /** @return array<string, mixed> */
    public function getMetaData(): array
    {
        if (!$this->markdown instanceof RenderedContentWithFrontMatter) {
            return [];
        }

        $frontMatter = $this->markdown->getFrontMatter();
        if (!is_array($frontMatter)) {
            throw new UnexpectedValueException('Front matter must be a YAML mapping');
        }

        $metaData = [];
        foreach ($frontMatter as $key => $value) {
            if (!is_string($key)) {
                throw new UnexpectedValueException('Front matter must be a YAML mapping');
            }
            $metaData[$key] = $value;
        }

        return $metaData;
    }
}
