<div id="myProjectsHub"
     hx-get="{{BASE_URL}}/hx/projects/projectHubProjects/get"
     hx-trigger="HTMX.updateProjectList from:body"
     hx-target="#myProjectsHub"
     hx-swap="outerHTML transition:true">

    @if (count($clients) > 0)
        <div class="dropdown dropdownWrapper pull-right">
            @php
                // Determine label text based on the current client name
                $labelText = $currentClientName != '' ? $currentClientName : __('headline.all_clients');
            @endphp
            <x-global::actions.dropdown :label-text="$labelText" contentRole="secondary" position="bottom" align="end"
                class=""
            >
                <x-slot:label-text>
                    @if ($currentClientName != '')
                        {{ $currentClientName }}
                    @else
                        {{ __("headline.all_clients") }}
                    @endif

                    <i class="fa fa-caret-down"></i>
                </x-slot:label-text>


                <x-slot:menu>
                    <!-- All Clients Option -->
                    <x-global::actions.dropdown.item href="{{ BASE_URL }}/projects/showMy">
                        {{ __('headline.all_clients') }}
                    </x-global::actions.dropdown.item>

                    <!-- Clients List -->
                    @foreach ($clients as $key => $value)
                        @if (!empty($key))
                            <x-global::actions.dropdown.item
                                href="javascript:void(0);"
                                hx-get="{{ BASE_URL }}/hx/projects/projectHubProjects/get?client={{ $key }}"
                                hx-target="#myProjectsHub" hx-swap="outerHTML transition:true">
                                {{ $value['name'] }}
                            </x-global::actions.dropdown.item>
                        @endif
                    @endforeach
                </x-slot:menu>

            </x-global::actions.dropdown>


        </div>
    @endif

    @if (count($allProjects) == 0)
        <br /><br />
        <div class='center'>
            <div style='width:70%; color:var(--main-action-color)' class='svgContainer'>
                {{ __('notifications.not_assigned_to_any_project') }}
                @if ($login::userIsAtLeast($roles::$manager))
                    <br />
                    <a href='{{ BASE_URL }}/projects/newProject'
                        class='btn btn-primary'>{{ __('link.new_project') }}</a>
                @endif
            </div>
        </div>
    @endif

    <x-global::content.accordion id="myProjectsHub-favorites" class="noBackground">
        <x-slot name="title">
            ⭐ My Favorites
        </x-slot>
        <x-slot name="content">
            <div class="row">
                @php
                    $hasFavorites = false;
                @endphp
                @foreach ($allProjects as $project)
                    @if ($project['isFavorite'] == true)
                        <div class="col-md-4">
                            @include("projects::components.projectCard", ["project" => $project,  "type" => "detailed"])
                        </div>
                        @php
                            $hasFavorites = true;
                        @endphp
                    @endif
                @endforeach
                @if ($hasFavorites === false)
                    <div style="color:var(--main-action-color)">
                        You don't have any favorites. 😿
                    </div>
                @endif
            </div>
        </x-slot>
    </x-global::content.accordion>


    <x-global::content.accordion id="myProjectsHub-otherProjects" class="noBackground">
        <x-slot name="title">
            🗂️ All Assigned Projects
        </x-slot>
        <x-slot name="content">

            <div class="row">
                @foreach ($allProjects as $project)
                    @if ($project['isFavorite'] == false)
                        <div class="col-md-3">
                            @include("projects::components.projectCard", ["project" => $project, "type" => "detailed"])
                        </div>
                    @endif
                @endforeach
            </div>
        </x-slot>
    </x-global::content.accordion>
</div>
