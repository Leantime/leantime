<div id="myProjectsHub"
     hx-get="{{BASE_URL}}/projects/projectHubProjects/get"
     hx-trigger="HTMX.updateProjectList from:body"
     hx-target="#myProjectsHub"
     hx-swap="outerHTML">

    @if (count($clients) > 0)
        <div class="dropdown dropdownWrapper tw:float-right">
            <a href="javascript:void(0)" class="btn btn-default dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                @if ($currentClientName != '')
                    {{ $currentClientName }}
                @else
                    {{ __("headline.all_clients") }}
                @endif

                <i class="fa fa-caret-down"></i>
            </a>

            <ul class="dropdown-menu">
                <li>
                    <a href="javascript:void(0);"
                       hx-get="{{BASE_URL}}/projects/projectHubProjects/get"
                       hx-target="#myProjectsHub"
                       hx-swap="outerHTML">{{ __("headline.all_clients") }}</a>
                </li>
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
        <br /><br />
        <div class='tw:text-center'>
            <div style='width:70%; color:var(--main-titles-color)' class='svgContainer'>
                {{ __('notifications.not_assigned_to_any_project') }}
                @if($login::userIsAtLeast($roles::$manager))
                    <br />
                    <a href='{{ BASE_URL }}/projects/newProject' class='btn btn-primary'>{{ __('link.new_project') }}</a>
                @endif
            </div>
        </div>
    @endif

    <x-global::accordion id="myProjectsHub-favorites" class="noBackground">
        <x-slot name="title">
            ⭐ My Favorites
        </x-slot>
        <x-slot name="content">
            <div class="tw:grid tw:grid-cols-3 tw:gap-6">
                @php
                    $hasFavorites = false;
                @endphp
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == true)
                        <div>
                            @include("projects::partials.projectCard", ["project" => $project,  "type" => "detailed"])
                        </div>
                        @php
                            $hasFavorites = true;
                        @endphp
                    @endif
                @endforeach
                @if($hasFavorites === false)
                    <div style="color:var(--main-titles-color)">
                        {{ __("text.no_favorites") }}
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

            <div class="tw:grid tw:grid-cols-4 tw:gap-4">
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == false)

                        <div>
                            @include("projects::partials.projectCard", ["project" => $project, "type" => "detailed"])
                        </div>

                    @endif

                @endforeach
            </div>
        </x-slot>
    </x-global::accordion>
</div>


