
@extends($layout)

@section('content')

    @props([
        'includeTitle' => true,
        'allProjects' => []
    ])

    @php

    Use Leantime\Core\Frontcontroller;
    $currentUrlPath = BASE_URL . "/" . str_replace(".", "/", Frontcontroller::getCurrentRoute());

    @endphp


    <div class="maincontent" style="margin-top:0px">

        <div style="padding:10px 0px">

            <div class="center">
                <span style="font-size:38px; color:var(--main-action-color);">

                    {{ __("headline.project_hub") }}

                </span><br />
                <span style="font-size:18px; color:var(--main-action-color);">
                   This is your project hub. All the projects you are currently assigned to or have favorited are here.

                    @if ($login::userIsAtLeast("manager"))
                        <br /><br /><a class="btn btn-default" href="#/projects/createnew">{!! __("menu.create_something_new") !!}</a>
                    @endif
                </span>
                <br />
                <br />
            </div>
        </div>

        {!! $tpl->displayNotification() !!}

        @if(is_array($allProjects) && count($allProjects) == 0)
            <x-global::undrawSvg image="undraw_a_moment_to_relax_bbpa.svg" headline="{{  __('notifications.not_assigned_to_any_project')  }}" maxWidth="30%">
            </x-global::undrawSvg>
        @endif

        <div id="myProjectsHub"
             hx-get="{{BASE_URL}}/projects/projectHubProjects/get"
             hx-trigger="HTMX.updateProjectList from:body"
             hx-target="#myProjectsHub"
             hx-swap="outerHTML transition:true">

            @if (count($clients) > 0)
                <div class="dropdown dropdownWrapper pull-right">
                    <a href="javascript:void(0)" class="btn btn-default dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                        @if ($currentClientName != '')
                            {{ $currentClientName }}
                        @else
                            {{ __("headline.all_clients") }}
                        @endif

                        <i class="fa fa-caret-down"></i>
                    </a>

                    <ul class="dropdown-menu">
                        <li><a href="{{ $currentUrlPath }}">{{ __("headline.all_clients") }}</a></li>
                        @foreach ($clients as $key => $value)
                            <li>
                                <a  href="javascript:void(0);"
                                    hx-get="{{BASE_URL}}/projects/projectHubProjects/get?client={{ $key }}"
                                    hx-target="#myProjectsHub"
                                    hx-swap="outerHTML transition:true">{{ $value['name'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
                            <div style="color:var(--main-action-color)">
                                You don't have any favorites. 😿
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

    </div>
@endsection

