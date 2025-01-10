@props([
    'project' => [],
    'variant' => 'simple', //compact, simple, full
    'formHash' => md5(CURRENT_URL."projectBox".mt_rand(0,100)),
])

<div {{ $attributes->merge(['class' => 'objectCard '.$variant ]) }} id="projectBox-{{ $formHash }}-{{ $project['id'] }}">
    <div class="row">
        <div class="col-md-12 fixed">
            <div class="row">
                <div  class="col-md-{{ ($variant !== 'compact') ? '11' : '12' }}">
                    <a href="{{ BASE_URL }}/dashboard/show?projectId={{ $project['id'] }}" class="objectLink">
                        <span class="avatar">
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
                @if($variant !== 'compact')
                    <div class="col-md-1 text-right">
                    <a  href="javascript:void(0);"
                        hx-patch="{{ BASE_URL }}/hx/projects/projectCard/toggleFavorite"
                        hx-vals='{"isFavorite": {{ $project['isFavorite'] }}, "projectId": {{ $project['id'] }}}'
                        hx-target="#projectBox-{{ $formHash }}-{{ $project['id'] }}"
                        onclick="jQuery(this).addClass('go')"
                        hx-swap="none"
                        class="favoriteClick favoriteStar btn btn-ghost btn-circle btn-sm pull-right {{ $project['isFavorite'] ? 'isFavorite' : ''}}"
                        data-tippy-content="{{ __('label.favorite_tooltip') }}">
                            <i class="{{ $project['isFavorite'] ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                    </a>
                </div>
                @endif
            </div>

            @if($variant != "simple" && $variant != "compact")
                <div id="projectProgressBox-{{ $formHash }}-{{ $project['id'] }}"
                    hx-get="{{ BASE_URL }}/hx/projects/projectCardProgress/getProgress?pId={{ $project['id'] }}"
                    hx-trigger="load"
                    hx-swap="innerHTML"
                    hx-target="#projectProgressBox-{{ $formHash }}-{{ $project['id'] }}"
                    hx-indicator=".htmx-indicator"
                    class="pt-md">
                    <div class="htmx-indicator">
                        <x-global::elements.loadingText type="card" count="1" />
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
