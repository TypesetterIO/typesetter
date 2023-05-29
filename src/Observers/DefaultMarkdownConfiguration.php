<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Observers;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;

class DefaultMarkdownConfiguration extends Observer
{
    public function __construct(protected array $languagesToProcess = ['html', 'php', 'js'])
    {
    }

    public function initializedMarkdownEnvironment(Environment $environment): void
    {
        $environment->addRenderer(FencedCode::class, new FencedCodeRenderer($this->languagesToProcess));
        $environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer($this->languagesToProcess));

        $environment->addExtension(new AttributesExtension());
    }
}
