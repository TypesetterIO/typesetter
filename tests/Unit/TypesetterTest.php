<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Typesetterio\Typesetter\Contracts\Observer;
use Typesetterio\Typesetter\Exceptions\ListenerInvalidException;
use Typesetterio\Typesetter\Typesetter;

class TypesetterTest extends TestCase
{
    // this test generates a warning - but that's ok
    public function testRegisterListenerNoImplementationsThrowsException(): void
    {
        $this->expectException(ListenerInvalidException::class);
        $this->expectExceptionMessage('not-a-class has failed to generate an implementation array.');

        $typesetter = new Typesetter();
        $typesetter->listen('not-a-class', function () {
        });
    }

    public function testRegisterListenersNotImplementEventContractThrowsException(): void
    {
        $this->expectException(ListenerInvalidException::class);
        $this->expectExceptionMessage('Typesetterio\Typesetter\Contracts\Observer does not implement the contract Typesetterio\Typesetter\Contracts\Event');

        $typesetter = new Typesetter();
        $typesetter->listen(Observer::class, function () {
        });
    }

    public function testGeneratedCoverNoTocNoFooterNoContentFiles(): void
    {
        $this->todo();
    }

    public function testHtmlCoverWithTocNoTocHeaderWithFooterWithFiles(): void
    {
        $this->todo();
    }

    public function testImageCover(): void
    {
        $this->todo();
    }
}
