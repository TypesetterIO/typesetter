<?php

declare(strict_types=1);

namespace Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function todo(): void
    {
        $this->markTestIncomplete('This is a todo test.');
    }
}
