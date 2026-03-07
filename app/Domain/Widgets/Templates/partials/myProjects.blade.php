@props([
    'includeTitle' => true,
    'allProjects' => [],
    'background' => ''
])

<div id="myProjectsWidget"
     hx-get="{{BASE_URL}}/widgets/myProjects/get"
     hx-trigger="HTMX.updateProjectList from:body"
     hx-target="#myProjectsWidget"
     hx-swap="outerHTML"
     aria-live="polite">
    @if (count($allProjects) == 0)
            <br /><br />
            <div class='center'>
                <div style='width:70%' class='svgContainer'>
                    {{ __('notifications.not_assigned_to_any_project') }}
                    @if($login::userIsAtLeast($roles::$manager))
                        <br /><br />
                        <x-globals::forms.button link="{{ BASE_URL }}/projects/newProject" type="primary">{{ __('link.new_project') }}</x-globals::forms.button>
                    @endif
                </div>
            </div>
    @endif
    <div class="clearall"></div>

    <x-globals::elements.accordion id="myProjectWidget-favorites" class="{{ $background }}">
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
                            <x-globals::projects.project-card :project="$project" :type="$type" :project-type-avatars="$projectTypeAvatars ?? []" />
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
    </x-globals::elements.accordion>


    <x-globals::elements.accordion id="myProjectWidget-otherProjects" class="{{ $background }}">
        <x-slot name="title">
            🗂️ All Assigned Projects
        </x-slot>
        <x-slot name="content">

            <div class="row">
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == false)

                        <div class="col-md-4">
                            <x-globals::projects.project-card :project="$project" :type="$type" :project-type-avatars="$projectTypeAvatars ?? []" />
                        </div>

                    @endif

                @endforeach
            </div>
        </x-slot>
    </x-globals::elements.accordion>

</div>

@dispatchEvent('afterMyProjectBox')
