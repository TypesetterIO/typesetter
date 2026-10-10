<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use League\CommonMark\Environment\Environment;
use Mpdf\Mpdf;
use Typesetterio\Typesetter\Contracts\Chapter;
use Typesetterio\Typesetter\Contracts\Observer;
use Typesetterio\Typesetter\Exceptions\InvalidConfigException;

/**
 * @implements IteratorAggregate<int, Observer>
 */
final class ObserverCollection implements Countable, IteratorAggregate
{
    /** @var list<Observer> */
    private array $observers = [];

    /** @param array<mixed> $observers */
    public function __construct(array $observers = [])
    {
        foreach ($observers as $key => $value) {
            if (!$value instanceof Observer) {
                throw new InvalidConfigException(sprintf('observers[%s] must be an instance of %s, %s given', $key, Observer::class, get_debug_type($value)));
            }
            $this->observers[] = $value;
        }
    }

    public function count(): int
    {
        return count($this->observers);
    }

    /** @return ArrayIterator<int, Observer> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->observers);
    }

    public function markdownEnvironmentInitialized(Environment $environment): self
    {
        foreach ($this->observers as $observer) {
            $observer->markdownEnvironmentInitialized($environment);
        }
        return $this;
    }

    public function pdfInitialized(Mpdf $mpdf): self
    {
        foreach ($this->observers as $observer) {
            $observer->pdfInitialized($mpdf);
        }
        return $this;
    }

    public function coverAdded(Mpdf $mpdf): self
    {
        foreach ($this->observers as $observer) {
            $observer->coverAdded($mpdf);
        }
        return $this;
    }

    public function chapterParsed(Chapter $chapter): self
    {
        foreach ($this->observers as $observer) {
            $observer->chapterParsed($chapter);
        }
        return $this;
    }
}
