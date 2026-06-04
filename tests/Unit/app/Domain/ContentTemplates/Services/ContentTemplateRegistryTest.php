<?php

namespace Unit\app\Domain\ContentTemplates\Services;

use Leantime\Domain\ContentTemplates\Models\ContentTemplate;
use Leantime\Domain\ContentTemplates\Services\ContentTemplateRegistry;
use Unit\TestCase;

/**
 * Unit tests for ContentTemplateRegistry.
 *
 * Sets up a tmp library root containing one logicmodel template and one wiki
 * template, then exercises load / forAppliesTo / get / overrides.
 */
class ContentTemplateRegistryTest extends TestCase
{
    private string $tmpRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpRoot = sys_get_temp_dir().'/ct-registry-test-'.uniqid();
        mkdir($this->tmpRoot.'/logicmodel', 0o777, true);
        mkdir($this->tmpRoot.'/wiki', 0o777, true);

        file_put_contents($this->tmpRoot.'/logicmodel/sample.yaml', <<<'YAML'
key: "sample"
title: "Sample LM"
description: "Test fixture."
appliesTo: "logicmodel"
sector: "test"
logicmodel:
  items:
    - box: "lm_inputs"
      title: "Item"
      description: "Desc"
YAML);

        file_put_contents($this->tmpRoot.'/wiki/notes.yaml', <<<'YAML'
key: "notes"
title: "Notes"
description: "Wiki test."
appliesTo: "wiki"
wiki:
  articles:
    - title: "Hello"
      content: "<p>Hi</p>"
YAML);
    }

    protected function tearDown(): void
    {
        $this->rmrf($this->tmpRoot);
        parent::tearDown();
    }

    public function test_for_applies_to_returns_templates_under_that_bucket(): void
    {
        $registry = new ContentTemplateRegistry;
        $registry->registerLibraryRoot($this->tmpRoot);

        $lm = $registry->forAppliesTo('logicmodel');
        $wiki = $registry->forAppliesTo('wiki');

        $this->assertCount(1, $lm);
        $this->assertArrayHasKey('sample', $lm);
        $this->assertInstanceOf(ContentTemplate::class, $lm['sample']);
        $this->assertSame('logicmodel', $lm['sample']->appliesTo);

        $this->assertCount(1, $wiki);
        $this->assertArrayHasKey('notes', $wiki);
        $this->assertSame('wiki', $wiki['notes']->appliesTo);
    }

    public function test_get_returns_single_template_by_applies_to_and_key(): void
    {
        $registry = new ContentTemplateRegistry;
        $registry->registerLibraryRoot($this->tmpRoot);

        $tpl = $registry->get('logicmodel', 'sample');

        $this->assertNotNull($tpl);
        $this->assertSame('Sample LM', $tpl->title);
        $this->assertSame('test', $tpl->sector);
    }

    public function test_get_returns_null_for_unknown_template(): void
    {
        $registry = new ContentTemplateRegistry;
        $registry->registerLibraryRoot($this->tmpRoot);

        $this->assertNull($registry->get('logicmodel', 'does-not-exist'));
        $this->assertNull($registry->get('unknown-applies-to', 'sample'));
    }

    public function test_directory_name_overrides_applies_to_in_yaml(): void
    {
        // YAML claims appliesTo=wiki but the file is in the logicmodel
        // directory. The registry should rewrite the appliesTo to match the
        // directory, so a typo in the YAML can't pollute the wrong bucket.
        file_put_contents($this->tmpRoot.'/logicmodel/lies.yaml', <<<'YAML'
key: "lies"
title: "Liar"
description: "Wrong appliesTo claim."
appliesTo: "wiki"
wiki:
  articles:
    - title: "Bait"
      content: ""
logicmodel:
  items:
    - box: "lm_inputs"
      title: "Item"
YAML);

        $registry = new ContentTemplateRegistry;
        $registry->registerLibraryRoot($this->tmpRoot);

        $byLm = $registry->get('logicmodel', 'lies');
        $this->assertNotNull($byLm);
        $this->assertSame('logicmodel', $byLm->appliesTo);

        $this->assertNull($registry->get('wiki', 'lies'));
    }

    public function test_later_library_root_overrides_earlier_on_collision(): void
    {
        $secondRoot = sys_get_temp_dir().'/ct-registry-test-second-'.uniqid();
        mkdir($secondRoot.'/logicmodel', 0o777, true);
        file_put_contents($secondRoot.'/logicmodel/sample.yaml', <<<'YAML'
key: "sample"
title: "Override Title"
description: "From second root."
appliesTo: "logicmodel"
logicmodel:
  items:
    - box: "lm_outputs"
      title: "Override Item"
YAML);

        try {
            $registry = new ContentTemplateRegistry;
            $registry->registerLibraryRoot($this->tmpRoot);
            $registry->registerLibraryRoot($secondRoot);

            $tpl = $registry->get('logicmodel', 'sample');

            $this->assertNotNull($tpl);
            $this->assertSame('Override Title', $tpl->title);
        } finally {
            $this->rmrf($secondRoot);
        }
    }

    public function test_register_library_root_is_idempotent(): void
    {
        $registry = new ContentTemplateRegistry;
        $registry->registerLibraryRoot($this->tmpRoot);
        $registry->registerLibraryRoot($this->tmpRoot);
        $registry->registerLibraryRoot($this->tmpRoot.'/');

        $lm = $registry->forAppliesTo('logicmodel');

        $this->assertCount(1, $lm);
    }

    public function test_invalid_yaml_is_skipped_without_crashing(): void
    {
        file_put_contents($this->tmpRoot.'/logicmodel/broken.yaml', "key: [unterminated\n");

        $registry = new ContentTemplateRegistry;
        $registry->registerLibraryRoot($this->tmpRoot);

        $lm = $registry->forAppliesTo('logicmodel');

        $this->assertCount(1, $lm);
        $this->assertArrayHasKey('sample', $lm);
        $this->assertArrayNotHasKey('broken', $lm);
    }

    private function rmrf(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $path = $dir.'/'.$f;
            is_dir($path) ? $this->rmrf($path) : unlink($path);
        }
        rmdir($dir);
    }
}
