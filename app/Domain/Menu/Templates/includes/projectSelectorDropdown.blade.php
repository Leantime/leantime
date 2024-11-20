@props([
    'redirect' => 'dashboard/show',
    'currentProject'
])

<div class="dropdown-content shadow-lg bg-base-200 w-[500px] rounded-box rounded-lg">
    @if ($menuType == 'project' || $menuType == 'default')
        <div class="p-4 border-b border-gray-200">
            <span class="text-gray-600 text-sm block">{{ __("menu.current_project") }}</span>
            <span class="text-gray-800 text-lg font-medium">{{ session("currentProjectName") }}</span>
        </div>
    @else
        <div class="border-b border-gray-200">
            <ul class="p-2 space-y-1" hx-boost="true" hx-indicator="#global-loader">
                <li>
                    <a href="{{ BASE_URL }}/projects/showMy" class="p-2 flex items-center text-gray-700 hover:bg-gray-50 rounded-md">
                        <i class="fa-solid fa-house-flag mr-2"></i> 
                        <strong>Open Project Hub</strong>
                    </a>
                </li>

                @if ($login::userIsAtLeast("manager"))
                    @dispatchEvent('beforeProjectCreateLink')
                    <li>
                        <a href="{{ $startSomethingUrl }}" class="p-2 flex items-center text-gray-700 hover:bg-gray-50 rounded-md">
                            {!! __('menu.create_something_new') !!}
                        </a>
                    </li>
                    @dispatchEvent('afterProjectCreateLink')
                @endif
            </ul>
        </div>
    @endif

    <div class="flex flex-col">
        <ul class="flex border-b border-gray-200 px-4">
            <li class="mr-4"><a href="#myProjects" class="inline-block py-2 px-1 text-gray-700 hover:text-primary-600 border-b-2 border-transparent hover:border-primary-600">{{ __('menu.projectselector.my_projects') }}</a></li>
            <li class="mr-4"><a href="#favorites" class="inline-block py-2 px-1 text-gray-700 hover:text-primary-600 border-b-2 border-transparent hover:border-primary-600">{{ __('menu.projectselector.favorites') }}</a></li>
            <li class="mr-4"><a href="#recentProjects" class="inline-block py-2 px-1 text-gray-700 hover:text-primary-600 border-b-2 border-transparent hover:border-primary-600">{{ __('menu.projectselector.recent') }}</a></li>
            <li class="mr-4"><a href="#allProjects" class="inline-block py-2 px-1 text-gray-700 hover:text-primary-600 border-b-2 border-transparent hover:border-primary-600">{{ __('menu.projectselector.all_projects') }}</a></li>
        </ul>

        <div id="myProjects" class="max-h-[calc(100vh-300px)] overflow-y-auto">
            @include('menu::includes.projectListFilter', ['clients' => $clients, 'projectSelectFilter' => $projectSelectFilter])
            <ul class="p-2 space-y-1">
                @if($projectSelectFilter["groupBy"] == "client")
                    @include('menu::includes.clientGroup', ['projects' => $allAssignedProjects, 'parent' => 0, 'level'=> 0, "prefix" => "myClientProjects", "currentProject"=>$currentProject])
                @elseif($projectSelectFilter["groupBy"] == "structure")
                    @include('menu::includes.projectGroup', ['projects' => $projectHierarchy, 'parent' => 0, 'level'=> 0, "prefix" => "myProjects", "currentProject"=>$currentProject])
                @else
                    @include('menu::includes.noGroup', ['projects' => $allAssignedProjects, "currentProject"=>$currentProject])
                @endif
            </ul>
        </div>

        <div id="allProjects" class="max-h-[calc(100vh-300px)] overflow-y-auto hidden">
            @include('menu::includes.projectListFilter', ['clients' => $clients, 'projectSelectFilter' => $projectSelectFilter])
            <ul class="p-2 space-y-1" hx-boost="true" hx-indicator="#global-loader">
                @if($projectSelectFilter["groupBy"] == "client")
                    @include('menu::includes.clientGroup', ['projects' => $allAvailableProjects, 'parent' => 0, 'level'=> 0, "prefix" => "allClientProjects", "currentProject"=>$currentProject])
                @elseif($projectSelectFilter["groupBy"] == "structure")
                    @include('menu::includes.projectGroup', ['projects' => $allAvailableProjectsHierarchy, 'parent' => 0, 'level'=> 0, "prefix" => "allProjects", "currentProject"=>$currentProject])
                @else
                    @include('menu::includes.noGroup', ['projects' => $allAvailableProjects, "currentProject"=>$currentProject])
                @endif
            </ul>
        </div>

        <div id="recentProjects" class="max-h-[calc(100vh-300px)] overflow-y-auto hidden">
            <ul class="p-2 space-y-1" hx-boost="true" hx-indicator="#global-loader">
                @if(count($recentProjects) >= 1)
                    @include('menu::includes.noGroup', ['projects' => $recentProjects])
                @else
                    <li class="p-4 text-gray-500">{{ __("menu.you_dont_have_projects") }}</li>
                @endif
            </ul>
        </div>

        <div id="favorites" class="max-h-[calc(100vh-300px)] overflow-y-auto hidden">
            <ul class="p-2 space-y-1" hx-boost="true" hx-indicator="#global-loader">
                @if(count($favoriteProjects) >= 1)
                    @include('menu::includes.noGroup', ['projects' => $favoriteProjects])
                @else
                    <li class="p-4 text-gray-500">{{ __("text.you_have_not_favorited_any_projects") }}</li>
                @endif
            </ul>
        </div>
    </div>

    @if ($menuType == 'project' || $menuType == 'default')
        <div class="border-t border-gray-200">
            <ul class="p-2 space-y-1" hx-boost="true" hx-indicator="#global-loader">
                @if ($login::userIsAtLeast('manager'))
                    @dispatchEvent('beforeProjectCreateLink')
                    <li>
                        <a href="{{ $startSomethingUrl }}" class="p-2 flex items-center text-gray-700 hover:bg-gray-50 rounded-md">
                            {!! __('menu.create_something_new') !!}
                        </a>
                    </li>
                    @dispatchEvent('afterProjectCreateLink')
                @endif

                <li>
                    <a href="{{ BASE_URL }}/projects/showMy" class="p-2 flex items-center text-gray-700 hover:bg-gray-50 rounded-md">
                        <i class="fa-solid fa-circle-nodes mr-2"></i> Project Hub
                    </a>
                </li>
            </ul>
        </div>
    @endif
</div>

<script>
    jQuery(document).ready(function() {
        // leantime.menuController.initProjectSelector();
    });
</script>
