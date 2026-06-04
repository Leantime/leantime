<?php

namespace Unit\app\Domain\ContentTemplates\Models;

use Leantime\Domain\ContentTemplates\Models\ContentTemplate;
use Unit\TestCase;

/**
 * Unit tests for the ContentTemplate value object.
 *
 * Two responsibilities:
 *  - From a parsed YAML array, pull metadata fields and the appliesTo-keyed payload.
 *  - Mark itself as "usable" only when key, appliesTo, and a non-empty payload are present.
 */
class ContentTemplateTest extends TestCase
{
    public function test_from_array_extracts_canvas_payload_under_applies_to_key(): void
    {
        $tpl = ContentTemplate::fromArray([
            'key' => 'education-k12',
            'title' => 'K-12 Education Program',
            'description' => 'After-school tutoring.',
            'appliesTo' => 'logicmodel',
            'sector' => 'education',
            'icon' => 'fa-graduation-cap',
            'author' => 'Leantime',
            'version' => '1.0.0',
            'license' => 'CC0',
            'logicmodel' => [
                'items' => [
                    ['box' => 'lm_inputs', 'title' => 'Funding', 'description' => 'Annual grants.'],
                ],
            ],
        ]);

        $this->assertSame('education-k12', $tpl->key);
        $this->assertSame('K-12 Education Program', $tpl->title);
        $this->assertSame('logicmodel', $tpl->appliesTo);
        $this->assertSame('education', $tpl->sector);
        $this->assertSame('fa-graduation-cap', $tpl->icon);
        $this->assertSame('Leantime', $tpl->author);
        $this->assertSame('1.0.0', $tpl->version);
        $this->assertSame('CC0', $tpl->license);
        $this->assertCount(1, $tpl->payload['items']);
        $this->assertTrue($tpl->isUsable());
    }

    public function test_from_array_extracts_wiki_payload_under_applies_to_key(): void
    {
        $tpl = ContentTemplate::fromArray([
            'key' => 'meeting-notes',
            'title' => 'Meeting Notes',
            'description' => 'Standard meeting template.',
            'appliesTo' => 'wiki',
            'wiki' => [
                'articles' => [
                    ['title' => 'Notes', 'content' => '<h1>Hi</h1>'],
                ],
            ],
        ]);

        $this->assertSame('wiki', $tpl->appliesTo);
        $this->assertCount(1, $tpl->payload['articles']);
        $this->assertTrue($tpl->isUsable());
    }

    public function test_optional_fields_default_to_null_when_missing(): void
    {
        $tpl = ContentTemplate::fromArray([
            'key' => 'x',
            'title' => 'X',
            'description' => '',
            'appliesTo' => 'logicmodel',
            'logicmodel' => ['items' => [['box' => 'a']]],
        ]);

        $this->assertNull($tpl->sector);
        $this->assertNull($tpl->icon);
        $this->assertNull($tpl->author);
        $this->assertNull($tpl->version);
        $this->assertNull($tpl->license);
    }

    public function test_is_usable_returns_false_for_missing_key_or_applies_to_or_empty_payload(): void
    {
        $missingKey = ContentTemplate::fromArray([
            'title' => 'X',
            'description' => '',
            'appliesTo' => 'logicmodel',
            'logicmodel' => ['items' => [['box' => 'a']]],
        ]);
        $missingAppliesTo = ContentTemplate::fromArray([
            'key' => 'x',
            'title' => 'X',
            'description' => '',
        ]);
        $emptyPayload = ContentTemplate::fromArray([
            'key' => 'x',
            'title' => 'X',
            'description' => '',
            'appliesTo' => 'logicmodel',
        ]);

        $this->assertFalse($missingKey->isUsable());
        $this->assertFalse($missingAppliesTo->isUsable());
        $this->assertFalse($emptyPayload->isUsable());
    }
}
