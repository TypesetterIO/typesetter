<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Typesetterio\Typesetter\Config;
use Typesetterio\Typesetter\Exceptions\TypesetterConfigException;
use Typesetterio\Typesetter\Observers\Observer;

class ConfigTest extends TestCase
{
    public function testMissingThemeFileThrowsException(): void
    {
        $this->expectException(TypesetterConfigException::class);
        $this->expectExceptionMessage('Missing theme.html: ./not-found/theme.html');

        $config = new Config(['theme' => './not-found']);
    }

    public function testAllDefaultValues(): void
    {
        chdir(__DIR__ . '/../data/theme');

        $config = new Config([]);

        self::assertEquals('.', $config->theme);
        self::assertEquals('My Typeset Book', $config->title);
        self::assertEquals('Joey Bubblegum', $config->author);
        self::assertTrue($config->tocEnabled);
        self::assertTrue($config->tocLinks);
        self::assertEquals('Table of Contents', $config->tocHeader);
        self::assertEquals('{PAGENO}', $config->footer);
        self::assertEquals(['md', 'markdown'], $config->markdownExtensions);
        self::assertTrue($config->observers->isEmpty());
    }

    public function testAllSpecifiedValues(): void
    {
        $config = new Config([
            'theme' => __DIR__ . '/../data/another-theme',
            'title' => 'The Title',
            'author' => 'The Author',
            'toc-enabled' => false,
            'toc-links' => false,
            'toc-header' => 'ToC for Me',
            'footer' => '',
            'markdown-extensions' => ['txt'],
            'observers' => [
                new class extends Observer {
                },
            ],
        ]);

        self::assertEquals(__DIR__ . '/../data/another-theme', $config->theme);
        self::assertEquals('The Title', $config->title);
        self::assertEquals('The Author', $config->author);
        self::assertFalse($config->tocEnabled);
        self::assertFalse($config->tocLinks);
        self::assertEquals('ToC for Me', $config->tocHeader);
        self::assertEquals('', $config->footer);
        self::assertEquals(['txt'], $config->markdownExtensions);
        self::assertFalse($config->observers->isEmpty());
    }
}
