@extends($layout)

@section('content')

@php
use Leantime\Domain\Wiki\Models\Template;

$today = date(__('language.dateformat'));
$author = session('userdata.name') . ' (' . session('userdata.mail') . ')';

// Document templates for the editor
// All Templates require title, description, content

$templates = [];

// All built-in document templates live as YAML in
// app/Domain/ContentTemplates/Library/wiki/ now. The registry block below
// loads them at request time, alongside any plugin-registered wiki
// templates. $today / $author are still in scope so plugins (via the
// documentTemplates filter, dispatched below) can keep using them.

// ── ContentTemplates registry — appliesTo:"wiki" ──
// Phase 3 of the content-templates rollout: plugins (and core) drop
// YAML files into ContentTemplates/wiki/ and they appear here.
//
// Each YAML string may carry t:KEY translation references; the
// TranslationResolver helper expands them at consume time so the
// user sees their locale's wording.
try {
    $registry = app(\Leantime\Domain\ContentTemplates\Services\ContentTemplateRegistry::class);
    foreach ($registry->forAppliesTo('wiki') as $contentTpl) {
        $tplObj = app()->make(Template::class);
        $tplObj->title = \Leantime\Domain\ContentTemplates\Support\TranslationResolver::resolve($contentTpl->title);
        $tplObj->description = \Leantime\Domain\ContentTemplates\Support\TranslationResolver::resolve($contentTpl->description);
        // Category — for wiki templates we reuse the existing `sector`
        // field (same concept, different domain vocabulary). Falls back to
        // "documents" when the YAML doesn't specify one, matching the
        // hardcoded templates' previous default.
        $tplObj->category = $contentTpl->sector !== null && $contentTpl->sector !== ''
            ? \Leantime\Domain\ContentTemplates\Support\TranslationResolver::resolve($contentTpl->sector)
            : __('templates.documents');
        $articles = (array) ($contentTpl->payload['articles'] ?? []);
        // For single-article templates we mirror the legacy "one HTML blob"
        // shape the editor expects. Multi-article wiki templates are out of
        // scope for the editor's "insert template" flow (they'd map to wiki
        // page creation, not editor insertion).
        $tplObj->content = is_array($articles[0] ?? null)
            ? \Leantime\Domain\ContentTemplates\Support\TranslationResolver::resolve((string) ($articles[0]['content'] ?? ''))
            : '';
        if ($tplObj->content !== '') {
            $templates[] = $tplObj;
        }
    }
} catch (\Throwable $e) {
    // Registry not available (boot ordering, install) — silently skip.
}

$templates = $tpl->dispatch_filter('documentTemplates', $templates);

echo json_encode($templates);
@endphp

@endsection
