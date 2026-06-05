@extends($layout)

@section('content')

@php
use Leantime\Domain\Wiki\Models\Template;

$today = date(__('language.dateformat'));
$author = session('userdata.name') . ' (' . session('userdata.mail') . ')';

// Document templates for the editor
// All Templates require title, description, content

$templates = [];

// Templates migrated to YAML in app/Domain/ContentTemplates/Library/wiki/:
//   meeting-notes, prd, project-outline, status-{green,yellow,red,gray}.
// They're loaded by the registry block below alongside any plugin-registered
// wiki templates. The three hardcoded ones below (user story, bug, feature
// request) stay in PHP because their content carries no __() calls and
// migrating them is a no-op gain — flagged for the next idle pass.

// User Story
$userStoryTpl = app()->make(Template::class);
$userStoryTpl->title = 'User Story';
$userStoryTpl->category = __('templates.todos');
$userStoryTpl->description = 'A template for an agile user story';
$userStoryTpl->content = '
<table style="border-collapse: collapse; width: 100.049%;" border="1">
<thead>
<tr>
<td style="width: 33.3333%;">Title:</td>
<td style="width: 33.3333%;">Priority:</td>
<td style="width: 33.3333%;">Estimate:</td>
</tr>
</thead>
<tbody>
<tr>
<td style="width: 100%;" colspan="3">
<h4><strong>User Story</strong></h4>
<p><strong>As a</strong> &lt;insert type of user&gt;</p>
<p><strong>I want to&nbsp;</strong>&lt;perform some task&gt;<br /><br /><strong>so that I can</strong> &lt;achieve some goal&gt;</p>
</td>
</tr>
<tr >
<td colspan="3" >
<p><strong>Acceptance Criteria:</strong></p>
<p><strong>Given&nbsp;</strong>&lt;some context&gt;<br /><br /><strong>When&nbsp; </strong>&lt;some action is carried out&gt;</p>
<p><strong>Then&nbsp;</strong>&lt;a set of observable outcomes should occur&gt;&nbsp;</p>
</td>
</tr>
</tbody>
</table>';
$templates[] = $userStoryTpl;

$bugTpl = app()->make(Template::class);
$bugTpl->title = 'Bug';
$bugTpl->category = __('templates.todos');
$bugTpl->description = 'A template for a bug report';
$bugTpl->content = '<table style="border-collapse: collapse; width: 100.051%;" border="1">
 <tbody>
 <tr>
 <td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Summary</strong></span></td>
 <td style="width: 82.7562%;">summarize the issue your are experiencing</td>
 </tr>
 <tr>
 <td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Environment</strong></span></td>
 <td style="width: 82.7562%;">describe the environment under which the problem occured (hosted, production, staging etc)</td>
 </tr>
 <tr>
 <td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Reproduction Steps</strong></span></td>
 <td style="width: 82.7562%;">Describe the steps to reproduce the problem<br />
 <ol>
 <li>Step 1</li>
 <li>Step 2</li>
 </ol>
 </td>
 </tr>
 <tr>
 <td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Expected Outcome</strong></span></td>
 <td style="width: 82.7562%;">describe what you expected would happen</td>
 </tr>
 <tr>
 <td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Actual Outcome</strong></span></td>
 <td style="width: 82.7562%;">describe what actually happened</td>
 </tr>
 <tr>
 <td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Impact</strong></span></td>
 <td style="width: 82.7562%;">describe how impactful this issue is to your workflow (eg can not work at all; delays my work; large inconvenience etc)</td>
 </tr>
 <tr>
 <td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Additional Details</strong></span></td>
 <td style="width: 82.7562%;">Anything else you would like to mention</td>
 </tr>
 </tbody>
 </table>';

$templates[] = $bugTpl;

$featureTpl = app()->make(Template::class);
$featureTpl->title = 'Feature Request';
$featureTpl->category = __('templates.todos');
$featureTpl->description = 'A template for a feature request';
$featureTpl->content = '<table style="border-collapse: collapse; width: 100.051%;" border="1">
<tbody>
<tr>
<td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Summary</strong></span></td>
<td style="width: 82.7562%;">summarize the feature you would like</td>
</tr>
<tr>
<td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Target User</strong></span></td>
<td style="width: 82.7562%;">describe who benefits most from this feature</td>
</tr>
<tr>
<td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Desired Timeline</strong></span></td>
<td style="width: 82.7562%;">when would you like to see it implemented</td>
</tr>
<tr>
<td style="width: 17.2438%; background-color: var(--accent1);"><span style="color: #ffffff;"><strong>Additional Details</strong></span></td>
<td style="width: 82.7562%;">any additional details</td>
</tr>
</tbody>
</table>';

$templates[] = $featureTpl;

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
