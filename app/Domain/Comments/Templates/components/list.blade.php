@props([
    'module',
    'moduleId',
])

<div
    id="comments-{{$module}}-{{$moduleId}}"
    hx-get="{{ BASE_URL }}/comments/comment-list/get?module={{ $module }}&moduleId={{ $moduleId }}"
    hx-trigger="load"
    hx-indicator=".htmx-indicator"
></div>
<div class="htmx-indicator commentsLoadingIndicator">
    Loading Comments ...<br /><br />
</div>
