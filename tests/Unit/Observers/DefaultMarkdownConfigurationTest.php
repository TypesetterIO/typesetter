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
        self::assertStringContainsString('class="language-php hljs php"', (string) $this->convert("```php\n<?php echo 1;\n```"));
    }

    public function testFencedCodeWithoutLanguageIsHighlighted(): void
    {
        self::assertStringContainsString('hljs', (string) $this->convert("```\nplain\n```"));
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
