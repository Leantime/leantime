@props([
    'project' => [],
    'type' => 'simple',
    'projectTypeAvatars' => [],
])

<div class="projectBox" id="projectBox-{{ $project['id'] }}">
    <div class="fixed">
        <div class="row">
            <div class="col-md-10">
                <a href="{{ BASE_URL }}/dashboard/show?projectId={{ $project['id'] }}">
                    <span class="projectAvatar">
                        @if(isset($projectTypeAvatars[$project["type"]]) && $projectTypeAvatars[$project["type"]] != "avatar")
                            <span class="{{ $projectTypeAvatars[$project["type"]] }}"></span>
                        @else
                            <img src='{{ BASE_URL }}/api/projects?projectAvatar={{ $project["id"] }}&v={{  format($project['modified'])->timestamp() }}' />
                        @endif
                    </span>
                    @if($project["clientName"] != '')
                        <small>{{ $project["clientName"] }}</small>
                    @else
                        <small>{{ __('projectType.'.$project["type"] ?? 'project') }}</small>
                    @endif
                    <strong>{{ $project['name'] }}</strong>
                </a>
            </div>
            <div class="col-md-2 align-right">
                <a  href="javascript:void(0);"
                    hx-patch="{{ BASE_URL }}/hx/projects/projectCard/toggleFavorite"
                    hx-vals='{"isFavorite": {{ $project['isFavorite'] }}, "projectId": {{ $project['id'] }}}'
                    hx-target="#projectBox-{{ $project['id'] }}"
                    onclick="jQuery(this).addClass('go')"
                    hx-swap="none"
                    class="favoriteClick favoriteStar pull-right margin-right {{ $project['isFavorite'] ? 'isFavorite' : ''}} tw:mr-[5px]"
                    data-tippy-content="{{ __('label.favorite_tooltip') }}"
                    aria-label="{{ __('label.favorite_tooltip') }}">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ $project['isFavorite'] ? '1' : '0' }};">star</span>
                </a>
            </div>
        </div>

        @if($type != "simple")
            <div class="projectBox-progress" id="projectProgressBox-{{ $project['id'] }}"
                hx-get="{{ BASE_URL }}/hx/projects/projectCardProgress/getProgress?pId={{ $project['id'] }}"
                hx-trigger="load"
                hx-swap="innerHTML"
                hx-target="#projectProgressBox-{{ $project['id'] }}"
                hx-indicator=".htmx-indicator"
                aria-live="polite">
                <div class="htmx-indicator" role="status">
                    <x-globals::feedback.skeleton type="card" count="1" />
                </div>
            </div>
        @endif

    </div>
</div>
