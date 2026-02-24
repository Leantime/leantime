<div id="myProjectsHub"
     hx-get="{{BASE_URL}}/projects/projectHubProjects/get"
     hx-trigger="HTMX.updateProjectList from:body"
     hx-target="#myProjectsHub"
     hx-swap="outerHTML"
     aria-live="polite">

    @if (count($clients) > 0)
        <x-global::elements.link-dropdown :label="$currentClientName != '' ? $currentClientName : __('headline.all_clients')" triggerClass="btn btn-default header-title-dropdown" class="pull-right">
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
        </x-global::elements.link-dropdown>
    @endif

    @if (count($allProjects) == 0)
        <br /><br />
        <div class='center'>
            <div style='width:70%; color:var(--main-titles-color)' class='svgContainer'>
                {{ __('notifications.not_assigned_to_any_project') }}
                @if($login::userIsAtLeast($roles::$manager))
                    <br />
                    <x-global::button link="{{ BASE_URL }}/projects/newProject" type="primary">{{ __('link.new_project') }}</x-global::button>
                @endif
            </div>
        </div>
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


