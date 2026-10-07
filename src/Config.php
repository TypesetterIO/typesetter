<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter;

use Closure;
use Typesetterio\Typesetter\Exceptions\TypesetterConfigException;
use Typesetterio\Typesetter\Observers\DefaultMarkdownConfiguration;

class Config
{
    public string $theme;

    public string $content;

    public Closure $contentFilter;

    public string $contentExtra;

    public string $title;

    public string $author;

    public bool $tocEnabled;

    public bool $tocLinks;

    public string $tocHeader;

    public string $footer;

    public array $markdownExtensions;

    public ObserverCollection $observers;

    public function __construct(array $config)
    {
        $this->theme = $config['theme'] ?? '.';
        $themeHtmlFile = $this->theme . '/theme.html';
        if (!is_readable($themeHtmlFile)) {
            throw new TypesetterConfigException('Missing theme.html: ' . $themeHtmlFile);
        }

        $this->content = $config['content'] ?? '.';
        if (!is_dir($this->content) || !is_readable($this->content)) {
            throw new TypesetterConfigException('Unable to find a readable content directory: ' . $this->content);
        }
        $this->contentFilter = $config['contentFilter'] ?? fn() => true;

        $this->contentExtra = $config['contentExtra'] ?? '';

        $this->title = $config['title'] ?? 'My Typeset Book';
        $this->author = $config['author'] ?? 'Joey Bubblegum';

        $this->tocEnabled = (bool) ($config['toc-enabled'] ?? true);
        $this->tocLinks = (bool) ($config['toc-links'] ?? true);
        $this->tocHeader = $config['toc-header'] ?? 'Table of Contents';

        $this->footer = $config['footer'] ?? '{PAGENO}';

        $this->markdownExtensions = $config['markdown-extensions'] ?? ['md', 'markdown'];

        $this->observers = new ObserverCollection($config['observers'] ?? [
            new DefaultMarkdownConfiguration(),
        ]);
    }
}
