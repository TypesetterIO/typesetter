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

/**
 * @implements IteratorAggregate<int, Observer>
 */
class ObserverCollection implements Countable, IteratorAggregate
{
    /** @var list<Observer> */
    protected array $observers;

    /** @param Observer[] $observers */
    public function __construct(array $observers = [])
    {
        $this->observers = array_values($observers);
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

    public function initializedMarkdownEnvironment(Environment $environment): self
    {
        foreach ($this->observers as $observer) {
            $observer->initializedMarkdownEnvironment($environment);
        }
        return $this;
    }

    public function initializedPdf(Mpdf $mpdf): self
    {
        foreach ($this->observers as $observer) {
            $observer->initializedPdf($mpdf);
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

    public function parsed(Chapter $chapter): self
    {
        foreach ($this->observers as $observer) {
            $observer->parsed($chapter);
        }
        return $this;
    }
}
