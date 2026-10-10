<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use Tests\TestCase;
use Typesetterio\Typesetter\Observers\DefaultMarkdownConfiguration;

class DefaultMarkdownConfigurationTest extends TestCase
{
    public function testFencedCodeIsHighlighted(): void
    {
        $html = (string) $this->convert("```php\n<?php echo 1;\n```");

        self::assertStringContainsString('<pre data-lang="php" class="notranslate">', $html);
        self::assertStringContainsString('<span class="hl-keyword">echo</span>', $html);
    }

    public function testFencedCodeWithoutLanguageIsPlainText(): void
    {
        self::assertSame(
            "<pre data-lang=\"txt\" class=\"notranslate\">echo &lt;b&gt;;\n</pre>\n",
            (string) $this->convert("```\necho <b>;\n```"),
        );
    }

    public function testInlineCodeIsNotHighlighted(): void
    {
        self::assertSame("<p><code>{php}echo 1;</code></p>\n", (string) $this->convert('`{php}echo 1;`'));
    }

    public function testAttributesAreApplied(): void
    {
        self::assertSame("<h1 class=\"foo\">Title</h1>\n", (string) $this->convert('# Title {.foo}'));
    }

    public function testFrontMatterIsParsed(): void
    {
        $result = $this->convert("---\ntitle: T\n---\n# H");

        self::assertInstanceOf(RenderedContentWithFrontMatter::class, $result);
        self::assertSame(['title' => 'T'], $result->getFrontMatter());
        self::assertStringNotContainsString('<hr', (string) $result);
        self::assertStringNotContainsString('<h2', (string) $result);
    }

    private function convert(string $markdown): RenderedContentInterface
    {
        $converter = new GithubFlavoredMarkdownConverter();
        (new DefaultMarkdownConfiguration())->markdownEnvironmentInitialized($converter->getEnvironment());

        return $converter->convert($markdown);
    }
}
