<?php

declare(strict_types=1);

namespace Tests\Unit;

use Closure;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use PHPUnit\Framework\Attributes\DataProvider;
use SplFileInfo;
use Tests\TestCase;
use Typesetterio\Typesetter\Config;
use Typesetterio\Typesetter\Exceptions\InvalidConfigException;
use Typesetterio\Typesetter\Observers\DefaultMarkdownConfiguration;
use Typesetterio\Typesetter\Observers\Observer;

class ConfigTest extends TestCase
{
    private string $cwd;

    // some tests chdir() to exercise the "." defaults
    protected function setUp(): void
    {
        $this->cwd = (string) getcwd();
    }

    protected function tearDown(): void
    {
        chdir($this->cwd);
    }

    public function testMissingThemeFileThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageIs('Missing theme.html: ./not-found/theme.html');

        new Config(['theme' => './not-found']);
    }

    public function testMissingContentDirectoryThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageIs('Unable to find a readable content directory: ./not-found');

        chdir(__DIR__ . '/../data/theme');

        new Config(['content' => './not-found']);
    }

    public function testMissingContentExtraDirectoryThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageIs('Unable to find a readable content-extra directory: ./nope');

        new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'content-extra' => './nope',
        ]);
    }

    public function testUnknownKeyThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageIs('Unknown config key(s): nope');

        new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'nope' => true,
        ]);
    }

    public function testAllDefaultValues(): void
    {
        chdir(__DIR__ . '/../data/theme');

        $config = new Config([]);

        self::assertEquals('.', $config->theme);
        self::assertEquals('.', $config->content);
        self::assertTrue(($config->contentFilter)(new SplFileInfo(__FILE__), basename(__FILE__)));
        self::assertSame('', $config->contentExtra);
        self::assertEquals('My Typeset Book', $config->title);
        self::assertEquals('Joey Bubblegum', $config->author);
        self::assertTrue($config->tocEnabled);
        self::assertTrue($config->tocLinks);
        self::assertEquals('Table of Contents', $config->tocHeader);
        self::assertSame(['H1' => 0, 'H2' => 1], $config->tocLevels);
        self::assertSame([], $config->tocAttributes);
        self::assertEquals('{PAGENO}', $config->footer);
        self::assertSame([
            'mode' => 'utf-8',
            'margin_left' => 27,
            'margin_right' => 27,
            'margin_bottom' => 14,
            'margin_top' => 14,
            'showImageErrors' => true,
        ], $config->mpdf);
        self::assertEquals(['md', 'markdown'], $config->markdownExtensions);
        self::assertCount(1, $config->observers);
        self::assertContainsOnlyInstancesOf(DefaultMarkdownConfiguration::class, $config->observers);
    }

    public function testAllSpecifiedValues(): void
    {
        $config = new Config([
            'theme' => __DIR__ . '/../data/another-theme',
            'content' => __DIR__ . '/../data/content',
            'content-filter' => fn(): bool => false,
            'content-extra' => __DIR__ . '/../data/content-extra',
            'title' => 'The Title',
            'author' => 'The Author',
            'toc-enabled' => false,
            'toc-links' => false,
            'toc-header' => 'ToC for Me',
            'toc-levels' => ['H1', 'H2', 'H3'],
            'toc-attributes' => ['resetpagenum' => 1, 'paging' => 'off'],
            'footer' => '',
            'mpdf' => ['format' => 'A5', 'margin_left' => 10],
            'markdown-extensions' => ['txt'],
            'observers' => [
                new class extends Observer {},
            ],
        ]);

        self::assertEquals(__DIR__ . '/../data/another-theme', $config->theme);
        self::assertEquals(__DIR__ . '/../data/content', $config->content);
        self::assertFalse(($config->contentFilter)(new SplFileInfo(__FILE__), basename(__FILE__)));
        self::assertSame(__DIR__ . '/../data/content-extra', $config->contentExtra);
        self::assertEquals('The Title', $config->title);
        self::assertEquals('The Author', $config->author);
        self::assertFalse($config->tocEnabled);
        self::assertFalse($config->tocLinks);
        self::assertEquals('ToC for Me', $config->tocHeader);
        self::assertSame(['H1' => 0, 'H2' => 1, 'H3' => 2], $config->tocLevels);
        self::assertSame(['resetpagenum' => 1, 'paging' => 'off'], $config->tocAttributes);
        self::assertEquals('', $config->footer);
        self::assertSame([
            'mode' => 'utf-8',
            'margin_left' => 10,
            'margin_right' => 27,
            'margin_bottom' => 14,
            'margin_top' => 14,
            'showImageErrors' => true,
            'format' => 'A5',
        ], $config->mpdf);
        self::assertEquals(['txt'], $config->markdownExtensions);
        self::assertCount(1, $config->observers);
    }

    public function testMpdfFontsExtendTheBundledFonts(): void
    {
        $config = new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'mpdf' => [
                'fontDir' => ['my-theme/fonts'],
                'fontdata' => [
                    'custom' => ['R' => 'custom.ttf'],
                    'dejavusans' => ['R' => 'replaced.ttf'],
                ],
            ],
        ]);

        $bundledFontDir = (new ConfigVariables())->getDefaults()['fontDir'];
        $bundledFontdata = (new FontVariables())->getDefaults()['fontdata'];
        self::assertSame([...$bundledFontDir, 'my-theme/fonts'], $config->mpdf['fontDir']);
        self::assertSame(
            ['custom' => ['R' => 'custom.ttf'], 'dejavusans' => ['R' => 'replaced.ttf']] + $bundledFontdata,
            $config->mpdf['fontdata'],
        );
    }

    public function testMpdfFontDirAcceptsASingleDirectory(): void
    {
        $config = new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'mpdf' => ['fontDir' => 'my-theme/fonts'],
        ]);

        $bundledFontDir = (new ConfigVariables())->getDefaults()['fontDir'];
        self::assertSame([...$bundledFontDir, 'my-theme/fonts'], $config->mpdf['fontDir']);
    }

    public function testUnknownMpdfOptionThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageIs('Unknown mpdf option(s): fontdir, Format');

        new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'mpdf' => ['fontdir' => 'fonts', 'Format' => 'A5', 'tempDir' => '/tmp'],
        ]);
    }

    public function testNoObservers(): void
    {
        $config = new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'observers' => [],
        ]);

        self::assertCount(0, $config->observers);
    }

    public function testStringCallableContentFilterBecomesClosure(): void
    {
        $config = new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'content-filter' => 'Tests\Fixtures\AcceptAllContentFilter::filter',
        ]);

        self::assertInstanceOf(Closure::class, $config->contentFilter);
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
        $this->expectExceptionMessageIs('toc-levels must be a non-empty list of unique heading tags H1 to H6');

        new Config([
            'theme' => __DIR__ . '/../data/theme',
            'content' => __DIR__ . '/../data/content',
            'toc-levels' => $tocLevels,
        ]);
    }
}
