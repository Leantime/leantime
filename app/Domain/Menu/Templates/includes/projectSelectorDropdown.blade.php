@props([
    'redirect' => 'dashboard/show',
    'currentProject'
])

<div class="dropdown-menu projectselector" id="mainProjectSelector">

    @if ($menuType == 'project' || $menuType == 'default')
        <div class="head">
            <span class="sub">{{ __("menu.current_project") }}</span><br />
            <span class="title">{{ session("currentProjectName") }}</span>
        </div>
    @else
        <div class="projectSelectorFooter" style="border:none; border-bottom:1px solid var(--main-border-color)">
        <ul class="selectorList projectList" hx-boost="true" hx-indicator="#global-loader">
            <li>
                <a href="{{ BASE_URL }}/projects/showMy"><strong><i class="fa-solid fa-house-flag"></i> Open Project Hub</strong></a>
            </li>

            @if ($login::userIsAtLeast("manager"))
                @dispatchEvent('beforeProjectCreateLink')
                <li><a href="{{ $startSomethingUrl }}">
                        <span class="fancyLink">
                            {!! __('menu.create_something_new') !!}
                        </span>
                    </a>
                </li>
                @dispatchEvent('afterProjectCreateLink')
            @endif

        </ul>
        </div>
    @endif

    <x-global::content.tabs class="overflow-y-scroll max-h-[500px] border-b !border-b-gray-500">
        <x-slot:headings>
            <x-global::content.tabs.heading name="myProjects">{{ __('menu.projectselector.my_projects') }}</x-global::content.tabs.heading>
            <x-global::content.tabs.heading name="favorites">{{ __('menu.projectselector.favorites') }}</x-global::content.tabs.heading>
            <x-global::content.tabs.heading name="recent">{{ __('menu.projectselector.recent') }}</x-global::content.tabs.heading>
            <x-global::content.tabs.heading name="allProjects">{{ __('menu.projectselector.all_projects') }}</x-global::content.tabs.heading>
        </x-slot:headings>
        <x-slot:contents>
            <x-global::content.tabs.content name="myProjects" class="">
                @include('menu::includes.projectListFilter', ['clients' => $clients, 'projectSelectFilter' => $projectSelectFilter])
                <ul class="selectorList projectList htmx-loaded-content">
                    @if($projectSelectFilter["groupBy"] == "client")
                        @include('menu::includes.clientGroup', ['projects' => $allAssignedProjects, 'parent' => 0, 'level'=> 0, "prefix" => "myClientProjects", "currentProject"=>$currentProject])
                    @elseif($projectSelectFilter["groupBy"] == "structure")
                        @include('menu::includes.projectGroup', ['projects' => $projectHierarchy, 'parent' => 0, 'level'=> 0, "prefix" => "myProjects", "currentProject"=>$currentProject])
                    @else
                        @include('menu::includes.noGroup', ['projects' => $allAssignedProjects, "currentProject"=>$currentProject])
                    @endif
                </ul>
            </x-global::content.tabs.content>

            <x-global::content.tabs.content name="favorites" class="">
                <ul class="selectorList projectList" hx-boost="true" hx-indicator="#global-loader">
                    @if(count($favoriteProjects) >= 1)
                        @include('menu::includes.noGroup', ['projects' => $favoriteProjects])
                    @else
                        <li><span class='info'>
                        {{ __("text.you_have_not_favorited_any_projects") }}
                        </span>
                        </li>
                    @endif
                </ul>
            </x-global::content.tabs.content>

            <x-global::content.tabs.content name="allProjects" class="">
                @include('menu::includes.projectListFilter', ['clients' => $clients, 'projectSelectFilter' => $projectSelectFilter])
                <ul class="selectorList projectList htmx-loaded-content"  hx-boost="true" hx-indicator="#global-loader">
                    @if($projectSelectFilter["groupBy"] == "client")
                        @include('menu::includes.clientGroup', ['projects' => $allAvailableProjects, 'parent' => 0, 'level'=> 0, "prefix" => "allClientProjects", "currentProject"=>$currentProject])
                    @elseif($projectSelectFilter["groupBy"] == "structure")
                        @include('menu::includes.projectGroup', ['projects' => $allAvailableProjectsHierarchy, 'parent' => 0, 'level'=> 0, "prefix" => "allProjects", "currentProject"=>$currentProject])
                    @else
                        @include('menu::includes.noGroup', ['projects' => $allAvailableProjects, "currentProject"=>$currentProject])
                    @endif
                </ul>
            </x-global::content.tabs.content>

            <x-global::content.tabs.content name="recent" class="">
                <ul class="selectorList projectList" hx-boost="true" hx-indicator="#global-loader">
                    @if(count($recentProjects) >= 1)
                        @include('menu::includes.noGroup', ['projects' => $recentProjects])
                    @else
                        <li class='nav-header'></li>
                        <li><span class='info'>
                        {{ __("menu.you_dont_have_projects") }}
                        </span>
                        </li>
                    @endif
                </ul>
            </x-global::content.tabs.content>

        </x-slot:contents>
    </x-global::content.tabs>



    @if ($menuType == 'project' || $menuType == 'default')
        <div class="projectSelectorFooter">
            <ul class="selectorList projectList" hx-boost="true" hx-indicator="#global-loader">

                @if ($login::userIsAtLeast("manager"))
                    @dispatchEvent('beforeProjectCreateLink')
                    <li><a href="{{ $startSomethingUrl }}">
                            <span class="fancyLink">
                                {!! __('menu.create_something_new') !!}
                            </span>
                        </a>
                    </li>
                    @dispatchEvent('afterProjectCreateLink')
                @endif


                    <li>
                        <a href="{{ BASE_URL }}/projects/showMy"><i class="fa-solid fa-circle-nodes"></i> Project Hub</a>
                    </li>

            </ul>
        </div>

            @endif

</div>

<script>
    jQuery(document).ready(function () {
        leantime.menuController.initProjectSelector();
    });
</script>
