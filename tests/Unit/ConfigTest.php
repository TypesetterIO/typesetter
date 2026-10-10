<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Typesetterio\Typesetter\Config;
use Typesetterio\Typesetter\Exceptions\InvalidConfigException;
use Typesetterio\Typesetter\Observers\DefaultMarkdownConfiguration;
use Typesetterio\Typesetter\Observers\Observer;

class ConfigTest extends TestCase
{
    public function testMissingThemeFileThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Missing theme.html: ./not-found/theme.html');

        $config = new Config(['theme' => './not-found']);
    }

    public function testMissingContentDirectoryThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Unable to find a readable content directory: ./not-found');

        chdir(__DIR__ . '/../data/theme');

        $config = new Config(['content' => './not-found']);
    }

    public function testAllDefaultValues(): void
    {
        chdir(__DIR__ . '/../data/theme');

        $config = new Config([]);

        self::assertEquals('.', $config->theme);
        self::assertEquals('.', $config->content);
        self::assertEquals('My Typeset Book', $config->title);
        self::assertEquals('Joey Bubblegum', $config->author);
        self::assertTrue($config->tocEnabled);
        self::assertTrue($config->tocLinks);
        self::assertEquals('Table of Contents', $config->tocHeader);
        self::assertSame(['H1' => 0, 'H2' => 1], $config->tocLevels);
        self::assertEquals('{PAGENO}', $config->footer);
        self::assertEquals(['md', 'markdown'], $config->markdownExtensions);
        self::assertCount(1, $config->observers);
        self::assertContainsOnlyInstancesOf(DefaultMarkdownConfiguration::class, $config->observers);
    }

    public function testAllSpecifiedValues(): void
    {
        $config = new Config([
            'theme' => __DIR__ . '/../data/another-theme',
            'content' => __DIR__ . '/../data/content',
            'title' => 'The Title',
            'author' => 'The Author',
            'toc-enabled' => false,
            'toc-links' => false,
            'toc-header' => 'ToC for Me',
            'toc-levels' => ['H1', 'H2', 'H3'],
            'footer' => '',
            'markdown-extensions' => ['txt'],
            'observers' => [
                new class extends Observer {
                },
            ],
        ]);

        self::assertEquals(__DIR__ . '/../data/another-theme', $config->theme);
        self::assertEquals(__DIR__ . '/../data/content', $config->content);
        self::assertEquals('The Title', $config->title);
        self::assertEquals('The Author', $config->author);
        self::assertFalse($config->tocEnabled);
        self::assertFalse($config->tocLinks);
        self::assertEquals('ToC for Me', $config->tocHeader);
        self::assertSame(['H1' => 0, 'H2' => 1, 'H3' => 2], $config->tocLevels);
        self::assertEquals('', $config->footer);
        self::assertEquals(['txt'], $config->markdownExtensions);
        self::assertCount(1, $config->observers);
    }

    /** @return array<string, array{list<string>}> */
    public static function invalidTocLevelsProvider(): array
    {
        return [
            'empty' => [[]],
            'unknown tag' => [['H1', 'H7']],
            'duplicate' => [['H1', 'H1']],
        ];
    }

    /** @param list<string> $tocLevels */
    #[DataProvider('invalidTocLevelsProvider')]
    public function testInvalidTocLevelsThrowsException(array $tocLevels): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('toc-levels must be a non-empty list of unique heading tags H1 to H6');

        new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'toc-levels' => $tocLevels,
        ]);
    }
}
