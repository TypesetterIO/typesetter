<?php

declare(strict_types=1);

namespace Typesetterio\Typesetter\Observers;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use Tempest\Highlight\CommonMark\CodeBlockRenderer;

class DefaultMarkdownConfiguration extends Observer
{
    public function markdownEnvironmentInitialized(Environment $environment): void
    {
        $environment->addRenderer(FencedCode::class, new CodeBlockRenderer());

        $environment->addExtension(new AttributesExtension());
        $environment->addExtension(new FrontMatterExtension());
    }
}
