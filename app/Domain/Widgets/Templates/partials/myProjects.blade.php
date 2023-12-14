@props([
    'includeTitle' => true,
    'allProjects' => []
])

<div id="myProjectsWidget"
     hx-get="{{BASE_URL}}/widgets/myProjects/get"
     hx-trigger="click from:.favoriteClick"
     hx-target="#myProjectsWidget"
     hx-swap="outerHTML transition:true">

    <h5 class="subtitle tw-pb-m">🚧 {{ __("headline.your_projects") }}</h5>
    @if (count($allProjects) == 0)
            <br /><br />
            <div class='center'>
                <div style='width:70%' class='svgContainer'>
                    {{ __('notifications.not_assigned_to_any_project') }}
                    @if($login::userIsAtLeast($roles::$manager))
                        <br /><br />
                        <a href='{{ BASE_URL }}/projects/newProject' class='btn btn-primary'>{{ __('link.new_project') }}</a>
                    @endif
                </div>
            </div>
    @endif

    <x-global::accordion id="myProjectWidget-favorites">
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
                            @include("projects::partials.projectCard", ["project" => $project])
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


    <x-global::accordion id="myProjectWidget-otherProjects">
        <x-slot name="title">
            🗂️ All Assigned Projects
        </x-slot>
        <x-slot name="content">

            <div class="row">
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == false)

                        <div class="col-md-4">
                            @include("projects::partials.projectCard", ["project" => $project])
                        </div>

                    @endif

                @endforeach
            </div>
        </x-slot>
    </x-global::accordion>

</div>

@dispatchEvent('afterMyProjectBox')
