<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter;

use Closure;
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

    public string $footer;

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
     *     footer?: string,
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
            'footer',
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

        $this->footer = $config['footer'] ?? '{PAGENO}';

        $this->markdownExtensions = $config['markdown-extensions'] ?? ['md', 'markdown'];

        $this->observers = new ObserverCollection($config['observers'] ?? [
            new DefaultMarkdownConfiguration(),
        ]);
    }
}
