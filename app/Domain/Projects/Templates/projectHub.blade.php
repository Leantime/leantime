@extends($layout)

@section('content')

    @props([
        'includeTitle' => true,
        'allProjects' => []
    ])

    <div class="maincontent" style="margin-top:0px">
        <div style="padding:10px 0px">
            <div class="center">
                <span style="font-size:38px; color:var(--main-titles-color););">
                    {{ __("headline.project_hub") }}
                </span><br />
                <span style="font-size:18px; color:var(--main-titles-color););">
                   {{ __("text.project_hub_intro") }}
                    @if ($login::userIsAtLeast("manager"))
                        <br /><br /><x-globals::forms.button link="#/projects/createnew" type="secondary">{!! __("menu.create_something_new") !!}</x-globals::forms.button>
                    @endif
                </span>
                <br />
                <br />
            </div>
        </div>

        @if(is_array($allProjects) && count($allProjects) == 0)
            <x-globals::undrawSvg image="undraw_a_moment_to_relax_bbpa.svg" style="color:var(--main-titles-color);" maxWidth="30%">
            </x-globals::undrawSvg>

        @endif

        <div id="myProjectsHub"
             hx-get="{{BASE_URL}}/projects/projectHubProjects/get"
             hx-trigger="HTMX.updateProjectList from:body"
             hx-target="#myProjectsHub"
             hx-select="#myProjectsHub"
             hx-swap="outerHTML"
             aria-live="polite">

            @if (count($clients) > 0)
                <x-globals::actions.dropdown-menu variant="link" trailing-visual="arrow_drop_down" :label="$currentClientName != '' ? $currentClientName : __('headline.all_clients')" trigger-class="btn btn-default header-title-dropdown" class="pull-right">
                        <li>
                            <a href="javascript:void(0);"
                               hx-get="{{BASE_URL}}/projects/projectHubProjects/get"
                               hx-target="#myProjectsHub"
                               hx-select="#myProjectsHub"
                               hx-swap="outerHTML">{{ __("headline.all_clients") }}</a>
                        </li>
                        @foreach ($clients as $key => $value)
                            <li>
                                <a  href="javascript:void(0);"
                                    hx-get="{{BASE_URL}}/projects/projectHubProjects/get?client={{ $key }}"
                                    hx-target="#myProjectsHub"
                                    hx-select="#myProjectsHub"
                                    hx-swap="outerHTML">{{ $value['name'] }}</a>
                            </li>
                        @endforeach
                </x-globals::actions.dropdown-menu>
            @endif

            @if (count($allProjects) == 0)
                <br /><br />
                <div class='center'>
                    <div style='width:70%; color:var(--main-titles-color)' class='svgContainer'>
                        {{ __('notifications.not_assigned_to_any_project') }}
                        @if($login::userIsAtLeast($roles::$manager))
                            <br /><br />
                            <x-globals::forms.button link="{{ BASE_URL }}/projects/newProject" type="primary">{{ __('link.new_project') }}</x-globals::forms.button>
                        @endif
                    </div>
                </div>
            @endif

            <x-globals::elements.accordion id="myProjectsHub-favorites" class="noBackground">
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
                                    <x-globals::projects.project-card :project="$project" type="detailed" :project-type-avatars="$projectTypeAvatars ?? []" />
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
            </x-globals::elements.accordion>


            <x-globals::elements.accordion id="myProjectsHub-otherProjects" class="noBackground">
                <x-slot name="title">
                    {{ __("text.all_assigned_projects")  }}
                </x-slot>
                <x-slot name="content">

                    <div class="row">
                        @foreach ($allProjects as $project)
                            @if($project['isFavorite'] == false)

                                <div class="col-md-3">
                                    <x-globals::projects.project-card :project="$project" type="detailed" :project-type-avatars="$projectTypeAvatars ?? []" />
                                </div>

                            @endif
                        @endforeach
                    </div>
                </x-slot>
            </x-globals::elements.accordion>
        </div>
    </div>
@endsection

