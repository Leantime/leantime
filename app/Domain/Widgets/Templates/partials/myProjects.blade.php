@props([
    'includeTitle' => true,
    'allProjects' => [],
    'background' => ''
])

<div id="myProjectsWidget"
     hx-get="{{BASE_URL}}/widgets/myProjects/get"
     hx-trigger="HTMX.updateProjectList from:body"
     hx-target="#myProjectsWidget"
     hx-swap="outerHTML">
    @if (count($allProjects) == 0)
            <br /><br />
            <div class='tw:text-center'>
                <div style='width:70%' class='svgContainer'>
                    {{ __('notifications.not_assigned_to_any_project') }}
                    @if($login::userIsAtLeast($roles::$manager))
                        <br /><br />
                        <a href='{{ BASE_URL }}/projects/newProject' class='btn btn-primary'>{{ __('link.new_project') }}</a>
                    @endif
                </div>
            </div>
    @endif
    <div class="clearall"></div>

    <x-global::accordion id="myProjectWidget-favorites" class="{{ $background }}">
        <x-slot name="title">
            ⭐ My Favorites
        </x-slot>
        <x-slot name="content">

            <div class="tw:grid tw:md:grid-cols-12 tw:gap-4">
                @php
                    $hasFavorites = false;
                @endphp
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == true)
                        <div class="tw:md:col-span-4">
                            @include("projects::partials.projectCard", ["project" => $project, "type" => $type])
                        </div>
                        @php
                            $hasFavorites = true;
                        @endphp
                    @endif
                @endforeach
                @if($hasFavorites === false)
                    You don't have any favorites. 😿
                @endif
            </div>
        </x-slot>
    </x-global::accordion>


    <x-global::accordion id="myProjectWidget-otherProjects" class="{{ $background }}">
        <x-slot name="title">
            🗂️ All Assigned Projects
        </x-slot>
        <x-slot name="content">

            <div class="tw:grid tw:md:grid-cols-12 tw:gap-4">
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == false)

                        <div class="tw:md:col-span-4">
                            @include("projects::partials.projectCard", ["project" => $project, "type" => $type])
                        </div>

                    @endif

                @endforeach
            </div>
        </x-slot>
    </x-global::accordion>

</div>

@dispatchEvent('afterMyProjectBox')
