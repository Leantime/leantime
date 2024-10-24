@props([
    'module',
    'moduleId',
])

<div
    id="comments-{{$module}}-{{$moduleId}}"
    hx-get="{{ BASE_URL }}/hx/comments/comment-list/get?module={{ $module }}&moduleId={{ $moduleId }}"
    hx-trigger="load"
    hx-swap="innerHTML"
    hx-target="#comments-{{$module}}-{{$moduleId}}"
>
    <!-- Loading message while the comments load -->
    <p>Loading comments...</p>
</div>
