<div id="myProjectsHub"
     hx-get="{{BASE_URL}}/projects/projectHubProjects/get"
     hx-trigger="HTMX.updateProjectList from:body"
     hx-target="#myProjectsHub"
     hx-swap="outerHTML">

    @if (count($clients) > 0)
        <div class="dropdown dropdownWrapper pull-right">
            <a href="javascript:void(0)" class="btn btn-default dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                @if ($currentClientName != '')
                    {{ $currentClientName }}
                @else
                    {{ __("headline.all_clients") }}
                @endif

                <i class="fa fa-caret-down"></i>
            </a>

            <ul class="dropdown-menu">
                <li><a href="{{ BASE_URL }}/projects/showMy">{{ __("headline.all_clients") }}</a></li>
                @foreach ($clients as $key => $value)
                    @if(! empty($key))
                        <li>
                            <a href="javascript:void(0);"
                               hx-get="{{BASE_URL}}/projects/projectHubProjects/get?client={{ $key }}"
                               hx-target="#myProjectsHub"
                               hx-swap="outerHTML">{{ $value['name'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    @if (count($allProjects) == 0)
        <x-global::emptyState
            icon="fa-suitcase"
            headline="{{ __('text.no_projects_yet') }}"
            description="{{ __('text.no_projects_hint') }}"
            actionLabel="{{ $login::userIsAtLeast($roles::$manager) ? __('link.new_project') : '' }}"
            actionHref="{{ BASE_URL }}/projects/newProject"
        />
    @endif

    <x-global::accordion id="myProjectsHub-favorites" class="noBackground">
        <x-slot name="title">
            ⭐ My Favorites
        </x-slot>
        <x-slot name="content">
            <div class="row">
                @php
                    $hasFavorites = false;
                @endphp
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == true)
                        <div class="col-md-4">
                            @include("projects::partials.projectCard", ["project" => $project,  "type" => "detailed"])
                        </div>
                        @php
                            $hasFavorites = true;
                        @endphp
                    @endif
                @endforeach
                @if($hasFavorites === false)
                    <div style="color:var(--main-action-color)">
                        You don't have any favorites. 😿
                    </div>
                @endif
            </div>
        </x-slot>
    </x-global::accordion>


    <x-global::accordion id="myProjectsHub-otherProjects" class="noBackground">
        <x-slot name="title">
            🗂️ All Assigned Projects
        </x-slot>
        <x-slot name="content">

            <div class="row">
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == false)

                        <div class="col-md-3">
                            @include("projects::partials.projectCard", ["project" => $project, "type" => "detailed"])
                        </div>

                    @endif

                @endforeach
            </div>
        </x-slot>
    </x-global::accordion>
</div>


