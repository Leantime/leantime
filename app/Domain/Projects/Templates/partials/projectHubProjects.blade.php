<div id="myProjectsHub"
     hx-get="{{BASE_URL}}/projects/projectHubProjects/get"
     hx-trigger="HTMX.updateProjectList from:body"
     hx-target="#myProjectsHub"
     hx-swap="outerHTML">

    @if (count($clients) > 0)
        <x-global::actions.dropdown variant="subject" as="div" class="pull-right">
            <x-slot:trigger class="btn btn-default">
                @if ($currentClientName != '')
                    {{ $currentClientName }}
                @else
                    {{ __("headline.all_clients") }}
                @endif

                <i class="fa fa-caret-down"></i>
            </x-slot:trigger>
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

        </x-global::actions.dropdown>
    @endif

    @if (count($allProjects) == 0)
        <br /><br />
        <div class='center'>
            <div style='width:70%; color:var(--main-action-color)' class='svgContainer'>
                {{ __('notifications.not_assigned_to_any_project') }}
                @if($login::userIsAtLeast($roles::$manager))
                    <br />
                    <x-global::forms.button tag="a" link="{{ BASE_URL }}/projects/newProject" contentRole="primary">{{ __('link.new_project') }}</x-global::forms.button>
                @endif
            </div>
        </div>
    @endif

    <x-global::elements.accordion id="myProjectsHub-favorites" open onPage>
<x-slot:title>⭐ {{ __('headlines.my_favorites') }}</x-slot:title>
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
                        {{ __('text.no_favorites') }}
                    </div>
                @endif
            </div>
        </x-global::elements.accordion>


    <x-global::elements.accordion id="myProjectsHub-otherProjects" open onPage>
<x-slot:title>🗂️ {{ __('headlines.all_assigned_projects') }}</x-slot:title>

            <div class="row">
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == false)

                        <div class="col-md-3">
                            @include("projects::partials.projectCard", ["project" => $project, "type" => "detailed"])
                        </div>

                    @endif

                @endforeach
            </div>
        </x-global::elements.accordion>
</div>


