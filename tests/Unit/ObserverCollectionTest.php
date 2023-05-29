<?php

declare(strict_types=1);

namespace Tests\Unit;

use League\CommonMark\Environment\Environment;
use Tests\TestCase;
use Typesetterio\Typesetter\Contracts\Chapter;
use Typesetterio\Typesetter\ObserverCollection;
use Typesetterio\Typesetter\Observers\Observer;

class ObserverCollectionTest extends TestCase
{
    public function testInitializedMarkdownEnvironment(): void
    {
        $ob1 = new class extends Observer {
            public function __construct(public bool $loaded = false)
            {
            }

            public function initializedMarkdownEnvironment(Environment $environment): void
            {
                $this->loaded = true;
            }
        };

        $ob2 = new class extends Observer {
            public function __construct(public bool $loaded = false)
            {
            }

            public function initializedMarkdownEnvironment(Environment $environment): void
            {
                $this->loaded = true;
            }
        };

        $ob3 = new class extends Observer {
            public function __construct(public bool $loaded = false)
            {
            }

            public function initializedMarkdownEnvironment(Environment $environment): void
            {
                $this->loaded = true;
            }
        };

        $collection = new ObserverCollection([$ob1, $ob2, $ob3]);
        $collection->initializedMarkdownEnvironment(new Environment());
        self::assertTrue($ob1->loaded);
        self::assertTrue($ob2->loaded);
        self::assertTrue($ob3->loaded);
    }

    public function testParsed(): void
    {
        $ob1 = new class extends Observer {
            public function __construct(public bool $loaded = false)
            {
            }

            public function parsed(Chapter $chapter): void
            {
                $this->loaded = true;
            }
        };

        $ob2 = new class extends Observer {
            public function __construct(public bool $loaded = false)
            {
            }

            public function parsed(Chapter $chapter): void
            {
                $this->loaded = true;
            }
        };

        $ob3 = new class extends Observer {
            public function __construct(public bool $loaded = false)
            {
            }

            public function parsed(Chapter $chapter): void
            {
                $this->loaded = true;
            }
        };

        $collection = new ObserverCollection([$ob1, $ob2, $ob3]);
        $collection->parsed(new \Typesetterio\Typesetter\Chapter('', 1, 1));
        self::assertTrue($ob1->loaded);
        self::assertTrue($ob2->loaded);
        self::assertTrue($ob3->loaded);
    }
}
