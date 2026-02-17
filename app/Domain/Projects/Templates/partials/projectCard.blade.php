@props([
    'project' => [],
    'type' => 'simple'
])

<div class="projectBox" id="projectBox-{{ $project['id'] }}">
    <div class="fixed">
        <div class="tw:grid tw:grid-cols-[1fr_auto] tw:gap-4 tw:pb-sm">
            <div>
                <a href="{{ BASE_URL }}/dashboard/show?projectId={{ $project['id'] }}">
                    <span class="projectAvatar">
                        @if(isset($projectTypeAvatars[$project["type"]]) && $projectTypeAvatars[$project["type"]] != "avatar")
                            <span class="{{ $projectTypeAvatars[$project["type"]] }}"></span>
                        @else
                            <img src='{{ BASE_URL }}/api/projects?projectAvatar={{ $project["id"] }}&v={{  format($project['modified'])->timestamp() }}' />
                        @endif
                    </span>
                    @if($project["clientName"] != '')
                        <small>{{ $project["clientName"] }}</small><br />
                    @else
                        <small>{{ __('projectType.'.$project["type"] ?? 'project') }}</small><br />
                    @endif
                    <strong>{{ $project['name'] }} <i class="fa-solid fa-up-right-from-square"></i></strong>
                </a>
            </div>
            <div class="tw:text-right">
                <a  href="javascript:void(0);"
                    hx-patch="{{ BASE_URL }}/hx/projects/projectCard/toggleFavorite"
                    hx-vals='{"isFavorite": {{ $project['isFavorite'] }}, "projectId": {{ $project['id'] }}}'
                    hx-target="#projectBox-{{ $project['id'] }}"
                    onclick="jQuery(this).addClass('go')"
                    hx-swap="none"
                    class="favoriteClick favoriteStar tw:float-right margin-right {{ $project['isFavorite'] ? 'isFavorite' : ''}} tw:mr-[5px]"
                    data-tippy-content="{{ __('label.favorite_tooltip') }}">
                        <i class="{{ $project['isFavorite'] ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                </a>
            </div>
        </div>

        @if($type != "simple")
            <div id="projectProgressBox-{{ $project['id'] }}"
                hx-get="{{ BASE_URL }}/hx/projects/projectCardProgress/getProgress?pId={{ $project['id'] }}"
                hx-trigger="load"
                hx-swap="innerHTML"
                hx-target="#projectProgressBox-{{ $project['id'] }}"
                hx-indicator=".htmx-indicator">
                <div class="htmx-indicator">
                    <x-global::loadingText type="card" count="1" />
                </div>
            </div>
        @endif

    </div>
</div>
