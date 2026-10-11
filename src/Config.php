<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter;

use Closure;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use SplFileInfo;
use Typesetterio\Typesetter\Exceptions\InvalidConfigException;
use Typesetterio\Typesetter\Observers\DefaultMarkdownConfiguration;

readonly class Config
{
    public string $theme;

    public string $content;

    /** @var Closure(SplFileInfo, string): bool */
    public Closure $contentFilter;

    public string $contentExtra;

    public string $title;

    public string $author;

    public bool $tocEnabled;

    public bool $tocLinks;

    public string $tocHeader;

    /** @var array<string, int> heading tag to TOC level, for example ['H1' => 0, 'H2' => 1] */
    public array $tocLevels;

    /** @var array<string, string|int> attributes merged over the library's <tocpagebreak> attributes */
    public array $tocAttributes;

    public string $footer;

    /** @var array<string, mixed> options for the mPDF constructor */
    public array $mpdf;

    /** @var list<string> */
    public array $markdownExtensions;

    public ObserverCollection $observers;

    /**
     * @param array{
     *     theme?: string,
     *     content?: string,
     *     'content-filter'?: callable(SplFileInfo, string): bool,
     *     'content-extra'?: string,
     *     title?: string,
     *     author?: string,
     *     'toc-enabled'?: bool,
     *     'toc-links'?: bool,
     *     'toc-header'?: string,
     *     'toc-levels'?: list<string>,
     *     'toc-attributes'?: array<string, string|int>,
     *     footer?: string,
     *     mpdf?: array{fontDir?: string|list<string>, fontdata?: array<string, array<string, mixed>>, ...<string, mixed>},
     *     'markdown-extensions'?: list<string>,
     *     observers?: list<\Typesetterio\Typesetter\Contracts\Observer>,
     * } $config
     */
    public function __construct(array $config)
    {
        $unknown = array_diff(array_keys($config), [
            'theme',
            'content',
            'content-filter',
            'content-extra',
            'title',
            'author',
            'toc-enabled',
            'toc-links',
            'toc-header',
            'toc-levels',
            'toc-attributes',
            'footer',
            'mpdf',
            'markdown-extensions',
            'observers',
        ]);
        if ($unknown !== []) {
            throw new InvalidConfigException('Unknown config key(s): ' . implode(', ', $unknown));
        }

        $this->theme = $config['theme'] ?? '.';
        $themeHtmlFile = $this->theme . '/theme.html';
        if (!is_readable($themeHtmlFile)) {
            throw new InvalidConfigException('Missing theme.html: ' . $themeHtmlFile);
        }

        $this->content = $config['content'] ?? '.';
        if (!is_dir($this->content) || !is_readable($this->content)) {
            throw new InvalidConfigException('Unable to find a readable content directory: ' . $this->content);
        }
        $this->contentFilter = Closure::fromCallable($config['content-filter'] ?? fn() => true);

        $this->contentExtra = $config['content-extra'] ?? '';
        if ($this->contentExtra !== '' && (!is_dir($this->contentExtra) || !is_readable($this->contentExtra))) {
            throw new InvalidConfigException('Unable to find a readable content-extra directory: ' . $this->contentExtra);
        }

        $this->title = $config['title'] ?? 'My Typeset Book';
        $this->author = $config['author'] ?? 'Joey Bubblegum';

        $this->tocEnabled = (bool) ($config['toc-enabled'] ?? true);
        $this->tocLinks = (bool) ($config['toc-links'] ?? true);
        $this->tocHeader = $config['toc-header'] ?? 'Table of Contents';

        $tocLevels = $config['toc-levels'] ?? ['H1', 'H2'];
        if (
            $tocLevels === []
            || array_diff($tocLevels, ['H1', 'H2', 'H3', 'H4', 'H5', 'H6']) !== []
            || count(array_unique($tocLevels)) !== count($tocLevels)
        ) {
            throw new InvalidConfigException('toc-levels must be a non-empty list of unique heading tags H1 to H6');
        }
        $this->tocLevels = array_flip($tocLevels);

        $this->tocAttributes = $config['toc-attributes'] ?? [];

        $this->footer = $config['footer'] ?? '{PAGENO}';

        $mpdfOptions = $config['mpdf'] ?? [];
        $mpdfDefaults = (new ConfigVariables())->getDefaults();
        $mpdfFontDefaults = (new FontVariables())->getDefaults();
        $unknownOptions = array_diff(
            array_keys($mpdfOptions),
            // read by Mpdf::initConstructorParams(), not part of ConfigVariables
            ['mode', 'format', 'orientation', 'default_font', 'default_font_size', 'margin_left', 'margin_right', 'margin_top', 'margin_bottom', 'margin_header', 'margin_footer'],
            array_keys($mpdfDefaults),
            array_keys($mpdfFontDefaults),
        );
        if ($unknownOptions !== []) {
            throw new InvalidConfigException('Unknown mpdf option(s): ' . implode(', ', $unknownOptions));
        }
        if (isset($mpdfOptions['fontDir'])) {
            $mpdfOptions['fontDir'] = [...$mpdfDefaults['fontDir'], ...(array) $mpdfOptions['fontDir']];
        }
        if (isset($mpdfOptions['fontdata'])) {
            $mpdfOptions['fontdata'] += $mpdfFontDefaults['fontdata'];
        }
        $this->mpdf = [
            'mode' => 'utf-8',
            'margin_left' => 27,
            'margin_right' => 27,
            'margin_bottom' => 14,
            'margin_top' => 14,
            'showImageErrors' => true,
            ...$mpdfOptions,
        ];

        $this->markdownExtensions = $config['markdown-extensions'] ?? ['md', 'markdown'];

        $this->observers = new ObserverCollection($config['observers'] ?? [
            new DefaultMarkdownConfiguration(),
        ]);
    }
}
