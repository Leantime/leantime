<?php

namespace Unit\app\Domain\ContentTemplates;

use Leantime\Domain\ContentTemplates\Services\ContentTemplateRegistry;
use Unit\TestCase;

/**
 * Integration check for Phase 3 of the content-templates rollout: wiki
 * YAML templates dropped into Library/wiki/ are discoverable via the
 * registry and carry the article shape the legacy template list expects.
 *
 * Not a controller test — that lives in Acceptance. This pins the data
 * contract between the YAML on disk and the consumer.
 */
class WikiTemplatesDiscoveryTest extends TestCase
{
    public function test_built_in_wiki_templates_are_discoverable_via_registry(): void
    {
        $registry = new ContentTemplateRegistry;

        $wikiTemplates = $registry->forAppliesTo('wiki');

        // Phase 3 ships at least the two demo templates (decision-record,
        // weekly-status). Asserting on count keeps this honest if either gets
        // removed.
        $this->assertGreaterThanOrEqual(2, count($wikiTemplates));
        $this->assertArrayHasKey('decision-record', $wikiTemplates);
        $this->assertArrayHasKey('weekly-status', $wikiTemplates);
    }

    public function test_built_in_wiki_template_has_single_article_with_html_content(): void
    {
        $registry = new ContentTemplateRegistry;
        $tpl = $registry->get('wiki', 'decision-record');

        $this->assertNotNull($tpl);
        $this->assertSame('wiki', $tpl->appliesTo);

        $articles = $tpl->payload['articles'] ?? [];
        $this->assertNotEmpty($articles);
        $this->assertIsArray($articles[0]);

        // The wiki Templates partial maps payload.articles[0].content into the
        // legacy Template->content field. HTML body, not markdown.
        $this->assertNotEmpty($articles[0]['content'] ?? '');
        $this->assertStringContainsString('<h1>', $articles[0]['content']);
    }
}
