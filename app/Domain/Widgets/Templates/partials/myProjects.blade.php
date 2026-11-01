@props([
    'includeTitle' => true,
    'allProjects' => [],
    'background' => ''
])

<div id="myProjectsWidget"
     hx-get="{{BASE_URL}}/widgets/myProjects/get"
     hx-trigger="{{ \Leantime\Domain\Projects\Events\ProjectFavoriteAdded::CLIENT_EVENT }} from:body, {{ \Leantime\Domain\Projects\Events\ProjectFavoriteRemoved::CLIENT_EVENT }} from:body"
     hx-target="#myProjectsWidget"
     hx-swap="outerHTML">
    @if (count($allProjects) == 0)
            <br /><br />
            <div class='center'>
                <div style='width:70%' class='svgContainer'>
                    {{ __('notifications.not_assigned_to_any_project') }}
                    @can('projects.create')
                        <br /><br />
                        <a href='{{ BASE_URL }}/projects/newProject' class='btn btn-primary'>{{ __('link.new_project') }}</a>
                    @endcan
                </div>
            </div>
    @endif
    <div class="clearall"></div>

    <x-global::elements.accordion id="myProjectWidget-favorites" :onPage="$background === 'noBackground'" open>
<x-slot:title>⭐ {{ __('headlines.my_favorites') }}</x-slot:title>

            <div class="row">
                @php
                    $hasFavorites = false;
                @endphp
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == true)
                        <div class="col-md-4">
                            @include("projects::partials.projectCard", ["project" => $project, "type" => $type])
                        </div>
                        @php
                            $hasFavorites = true;
                        @endphp
                    @endif
                @endforeach
                @if($hasFavorites === false)
                    {{ __('text.no_favorites') }}
                @endif
            </div>
        </x-global::elements.accordion>


    <x-global::elements.accordion id="myProjectWidget-otherProjects" :onPage="$background === 'noBackground'" open>
<x-slot:title>🗂️ {{ __('headlines.all_assigned_projects') }}</x-slot:title>

            <div class="row">
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == false)

                        <div class="col-md-4">
                            @include("projects::partials.projectCard", ["project" => $project, "type" => $type])
                        </div>

                    @endif

                @endforeach
            </div>
        </x-global::elements.accordion>

</div>

@dispatchEvent('afterMyProjectBox')
