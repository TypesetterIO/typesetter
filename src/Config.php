<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter;

use Illuminate\Support\Arr;
use Typesetterio\Typesetter\Exceptions\TypesetterConfigException;

class Config
{
    public string $theme;

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
        $this->theme = Arr::get($config, 'theme', '.');
        $themeHtmlFile = $this->theme . '/theme.html';
        if (!is_readable($themeHtmlFile)) {
            throw new TypesetterConfigException('Missing theme.html: ' . $themeHtmlFile);
        }

        $this->title = Arr::get($config, 'title', 'My Typeset Book');
        $this->author = Arr::get($config, 'author', 'Joey Bubblegum');

        $this->tocEnabled = (bool) Arr::get($config, 'toc-enabled', true);
        $this->tocLinks = (bool) Arr::get($config, 'toc-links', true);
        $this->tocHeader = Arr::get($config, 'toc-header', 'Table of Contents');

        $this->footer = Arr::get($config, 'footer', '{PAGENO}');

        $this->markdownExtensions = Arr::get($config, 'markdown-extensions', ['md', 'markdown']);

        $this->observers = new ObserverCollection(Arr::get($config, 'observers', []));
    }
}
