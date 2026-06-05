@extends($layout)

@section('content')

@php
use Leantime\Domain\Wiki\Models\Template;

$today = date(__('language.dateformat'));
$author = session('userdata.name') . ' (' . session('userdata.mail') . ')';

// Document templates for the editor
// All Templates require title, description, content

$templates = [];

// Meeting Notes migrated to YAML — see app/Domain/ContentTemplates/Library/wiki/meeting-notes.yaml
// (loaded via the registry below).

$prdTpl = app()->make(Template::class);

$prdTpl->title = __('templates.prd.title');
$prdTpl->description = __('templates.prd.description');
$prdTpl->category = __('templates.documents');
$prdTpl->content = '
<h1><strong>' . __('templates.prd.title_for_prd') . '<br /></strong></h1>
<p>' . __('templates.author') . ' ' . $author . '<br />
' . __('templates.dates') . ' ' . $today . '<br />
' . __('templates.status') . ' <span class="label label-default">' . __('templates.status.draft') . '</span><br />

<table style="border-collapse: collapse; width: 100%;" border="1">
<thead>
<tr>
<td style="width: 23.3025%;">' . __('templates.prd.responsible') . '</td>
<td style="width: 23.3025%;">' . __('templates.prd.approve') . '</td>
<td style="width: 23.3025%;">' . __('templates.prd.consulted') . '</td>
<td style="width: 23.3025%;">' . __('templates.prd.informed') . '</td>
</tr>
</thead>
<tbody>
<tr>
<td style="width: 23.3025%;">&nbsp;</td>
<td style="width: 23.3025%;">&nbsp;</td>
<td style="width: 23.3025%;">&nbsp;</td>
<td style="width: 23.3025%;">&nbsp;</td>
</tr>
</tbody>
</table>
<h1>' . __('templates.summary') . '</h1>
<h2>' . __('templates.overview') . '</h2>
<p>' . __('templates.prd.overview_description') . '</p>

<h2>' . __('templates.problem') . '</h2>
<p>' . __('templates.prd.problem_description') . '</p>

<h2 >Goals (What are we working towards?</h2>
<p>1. Goal</p>
<p>2. Goal</p>
<p>3. Goal</p>
<h2>Risks (Things that could get in the way or stop progress)</h2>
<p>1. Risk</p>
<p>2. Risk</p>
<p>3. Risk</p>
<h2>Who is the Customer &amp; Description</h2>
<p>Who are the target personas for this product, and which is the key persona?</p>
<table style="border-collapse: collapse; width: 50%;" border="1">
<tbody>
<tr style="height: 17px;">
<td style="width: 11.8519%; height: 17px;">Customer</td>
<td style="width: 88.1481%; height: 17px;">Description (interests, likes, demographics, where to find them)</td>
</tr>
<tr style="height: 17px;">
<td style="width: 11.8519%; height: 17px;">Customer</td>
<td style="width: 88.1481%; height: 17px;">Description (interests, likes, demographics, where to find them)</td>
</tr>
<tr>
<td style="width: 11.8519%;">Customer</td>
<td style="width: 88.1481%;">Description (interests, likes, demographics, where to find them)</td>
</tr>
</tbody>
</table>
<h2 >How will the customer use the product?</h2>
<p>Instances where various personas will use the product, in context.</p>
<h3>Use case</h3>
<p>Describe the use case</p>
<h3>Use case</h3>
<p>Describe the use case</p>
<h3>Use case</h3>
<p>Describe the use case</p>
<h1>Product Details</h1>
<p>When you&rsquo;ve locked in your One Pager, build out your PRD. Use the finalized One Pager and the following.</p>
<h2>Features - Must haves</h2>
<p>These are the distinct, prioritized features along with a short explanation of why this feature is important. Briefly outline the scope, the goals, and use case.</p>
<ul>
<li>Feature</li>
<li>Feature</li>
<li>Feature</li>
</ul>
<h2>Features - Nice to Haves</h2>
<ul>
<li>Feature</li>
<li>Feature</li>
<li>Feature</li>
</ul>
<h2>Features - Absolutely Not</h2>
<p>What features have you explicitly decided not to do and why?</p>
<ul>
<li>Feature</li>
<li>Feature</li>
<li>Feature</li>
</ul>
<h2>Design - Any files, images, wireframes or details go here (link to idea board)</h2>
<p>Include any needed early sketches, and throughout the project, link to the actual designs once they&rsquo;re available.</p>
<h2>echnical Considerations - (optional)</h2>
<p>Link to engineering technical approach document.</p>
<h2>Success Metrics</h2>
<p class="c8">What are the&nbsp;<a class="c35" href="https://www.google.com/url?q=https://productschool.com/blog/data-analytics/metrics-product-managers-measure/&amp;sa=D&amp;source=editors&amp;ust=1680296200488974&amp;usg=AOvVaw0DDV-fM6FNiXcUAQjmi42e">success metrics</a>that indicate you&rsquo;re achieving your internal goals for the project? How will you measure success?&nbsp;You can use any goal-setting and tracking system you prefer (OKRs, KPIs, etc).</p>
<p>Note:</span><span class="c58">&nbsp;Link to Analytics requirements and approach document.</span></p>
<h2>GTM Approach</h2>
<p>What&rsquo;s the product messaging that your &nbsp;marketing department will use to describe this product to existing and new customers? How do you plan to launch this product to the market with marketing and sales teams?</p>
<p>Note:</span><span class="c20 c63 c56 c58">&nbsp;Link to a larger GTM brief if available.</p>
<h2>Open Issues</h2>
<p>What factors do you still need to figure out? What problems may arise and how do you plan on addressing them?</p>
<h2>Q&amp;A</h2>
<p>What are common questions about the product along with the answers you&rsquo;ve decided? This is a good place to note key decisions.</p>
<table style="border-collapse: collapse; width: 100.041%;" border="1">
<thead>
<tr>
<td style="width: 23.3025%;">Question</td>
<td style="width: 23.3025%;">Answer</td>
<td style="width: 23.3025%;">Asked By</td>
<td style="width: 23.3025%;">Answered By</td>
</tr>
</thead>
<tbody>
<tr>
<td style="width: 23.3025%;">&nbsp;</td>
<td style="width: 23.3025%;">&nbsp;</td>
<td style="width: 23.3025%;">&nbsp;</td>
<td style="width: 23.3025%;">&nbsp;</td>
</tr>
</tbody>
</table>
<h2>PRD Checklist</span></h2>
<p class="c8">Here&rsquo;s a list of topics you must include in your PRD:</p>
<ul data-type="taskList">
<li data-type="taskItem" data-checked="false">Title</li>
<li data-type="taskItem" data-checked="false">Author</li>
<li data-type="taskItem" data-checked="false">Decision Log</li>
<li data-type="taskItem" data-checked="false">Change History</li>
<li data-type="taskItem" data-checked="false">Overview</li>
<li data-type="taskItem" data-checked="false">Messaging</li>
<li data-type="taskItem" data-checked="false">Personas</li>
<li data-type="taskItem" data-checked="false">User Scenarios</li>
<li data-type="taskItem" data-checked="false">User Stories/Features/Requirements</li>
<li data-type="taskItem" data-checked="false">Design</li>
<li data-type="taskItem" data-checked="false">Open Issues</li>
<li data-type="taskItem" data-checked="false">Q&amp;A</li>
</ul>
';
$templates[] = $prdTpl;

// Project Outline migrated to YAML — see app/Domain/ContentTemplates/Library/wiki/project-outline.yaml

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

$labelGreen = app()->make(Template::class);
$labelGreen->title = __('templates.titles.green_status');
$labelGreen->category = __('templates.elements');
$labelGreen->description = __('templates.titles.green_status_description');
$labelGreen->content = '<span class="label label-success">Green</span>';
$templates[] = $labelGreen;

$labelYellow = app()->make(Template::class);
$labelYellow->title = __('templates.titles.yellow_status');
$labelYellow->category = __('templates.elements');
$labelYellow->description = __('templates.titles.yellow_status_description');
$labelYellow->content = '<span class="label label-warning">Yellow</span>';
$templates[] = $labelYellow;

$labelRed = app()->make(Template::class);
$labelRed->title = __('templates.titles.red_status');
$labelRed->category = __('templates.elements');
$labelRed->description = __('templates.titles.red_status_description');
$labelRed->content = '<span class="label label-danger">Red</span>';
$templates[] = $labelRed;

$labelGray = app()->make(Template::class);
$labelGray->title = __('templates.titles.gray_status');
$labelGray->category = __('templates.elements');
$labelGray->description = __('templates.titles.gray_status_description');
$labelGray->content = '<span class="label label-default">Gray</span>';
$templates[] = $labelGray;

// ── ContentTemplates registry — appliesTo:"wiki" ──
// Phase 3 of the content-templates rollout: plugins (and core) drop
// YAML files into ContentTemplates/wiki/ and they appear here. As
// hardcoded templates migrate to YAML their inline blocks above
// disappear; PRD + the four status labels still live in PHP because
// their HTML content uses many __() calls that would need their own
// resolver pass — flagged for a follow-up sweep, not blocking.
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
