@props([
    'project' => [],
    'variant' => 'simple' //compact, simple, full
])

<div class="projectBox" id="projectBox-{{ $project['id'] }}">
    <div class="row">
        <div class="col-md-12 fixed">
            <div class="row pb-sm">
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
                            <small>{{ $project["clientName"] }}</small><br />
                        @else
                            <small>{{ __('projectType.'.$project["type"] ?? 'project') }}</small><br />
                        @endif
                        <strong>{{ $project['name'] }} <i class="fa-solid fa-up-right-from-square"></i></strong>
                    </a>
                </div>
                <div class="col-md-2 text-right">
                    <a  href="javascript:void(0);"
                        hx-patch="{{ BASE_URL }}/hx/projects/projectCard/toggleFavorite"
                        hx-vals='{"isFavorite": {{ $project['isFavorite'] }}, "projectId": {{ $project['id'] }}}'
                        hx-target="#projectBox-{{ $project['id'] }}"
                        onclick="jQuery(this).addClass('go')"
                        hx-swap="none"
                        class="favoriteClick favoriteStar pull-right margin-right {{ $project['isFavorite'] ? 'isFavorite' : ''}} mr-[5px]"
                        data-tippy-content="{{ __('label.favorite_tooltip') }}">
                            <i class="{{ $project['isFavorite'] ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                    </a>
                </div>
            </div>

            @if($variant != "simple")
                <div id="projectProgressBox-{{ $project['id'] }}"
                    hx-get="{{ BASE_URL }}/hx/projects/projectCardProgress/getProgress?pId={{ $project['id'] }}"
                    hx-trigger="load"
                    hx-swap="innerHTML"
                    hx-target="#projectProgressBox-{{ $project['id'] }}"
                    hx-indicator=".htmx-indicator">
                    <div class="htmx-indicator">
                        <x-global::elements.loadingText type="card" count="1" />
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
