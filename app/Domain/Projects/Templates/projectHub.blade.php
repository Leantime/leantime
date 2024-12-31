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
                        <br /><br />
                        <x-global::forms.button tag="a" content-role="secondary" href="#/projects/createnew">
                            {!! __("menu.create_something_new") !!}
                        </x-global::forms.button>
                    @endif
                </span>
                <br />
                <br />
            </div>
        </div>

        @if(is_array($allProjects) && count($allProjects) == 0)
            <x-global::elements.undrawSvg image="undraw_a_moment_to_relax_bbpa.svg" style="color:var(--main-titles-color);" maxWidth="30%">
            </x-global::elements.undrawSvg>

        @endif

        <div id="myProjectsHub"
             hx-get="{{BASE_URL}}/hx/projects/projectHubProjects/get"
             hx-trigger="HTMX.updateProjectList from:body"
             hx-target="#myProjectsHub"
             hx-swap="outerHTML transition:true">

            @if (count($clients) > 0)
                <div class="dropdown dropdownWrapper pull-right">
                    <x-global::actions.dropdown content-role="secondary" position="bottom" align="end"
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
                            <x-global::actions.dropdown.item variant="link"
                                href="{{ CURRENT_URL }}"
                            >
                                {{ __("headline.all_clients") }}
                            </x-global::actions.dropdown.item>
                        
                            <!-- Clients List -->
                            @foreach ($clients as $key => $value)
                                <x-global::actions.dropdown.item variant="link"
                                    href="javascript:void(0);"
                                    hx-get="{{BASE_URL}}/hx/projects/projectHubProjects/get?client={{ $key }}"
                                    hx-target="#myProjectsHub"
                                    hx-swap="outerHTML transition:true"
                                >
                                    {{ $value['name'] }}
                                </x-global::actions.dropdown.item>
                            @endforeach
                            
                        </x-slot:menu>
                    
                    </x-global::actions.dropdown>

                </div>
            @endif

            @if (count($allProjects) == 0)
                <br /><br />
                <div class='center'>
                    <div style='width:70%; color:var(--main-titles-color)' class='svgContainer'>
                        {{ __('notifications.not_assigned_to_any_project') }}
                        @if($login::userIsAtLeast($roles::$manager))
                            <br /><br />
                            <a href='{{ BASE_URL }}/projects/newProject' class='btn btn-primary'>{{ __('link.new_project') }}</a>
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
                            @if($project['isFavorite'] == true)
                                <div class="col-md-3">
                                    @include("projects::includes.projectCard", ["project" => $project,  "type" => "detailed"])
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
            </x-global::content.accordion>


            <x-global::content.accordion id="myProjectsHub-otherProjects" class="noBackground">
                <x-slot name="title">
                    {{ __("text.all_assigned_projects")  }}
                </x-slot>
                <x-slot name="content">

                    <div class="row">
                        @foreach ($allProjects as $project)
                            @if($project['isFavorite'] == false)

                                <div class="col-md-3">
                                    @include("projects::includes.projectCard", ["project" => $project, "type" => "detailed"])
                                </div>

                            @endif
                        @endforeach
                    </div>
                </x-slot>
            </x-global::content.accordion>
        </div>
    </div>
@endsection

