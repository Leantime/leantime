@props([
    'redirect' => 'dashboard/show'
])

<div class="dropdown-menu projectselector" id="mainProjectSelector">
    <div class="head">
        <span class="sub">{{ __("menu.current_project") }}</span><br />
        <span class="title">{{ $_SESSION['currentProjectName'] }}</span>
    </div>
    <div class="tabbedwidget tab-primary projectSelectorTabs">
        <ul class="tabs">
            <li><a href="#myProjects">My Projects</a></li>
            <li><a href="#allProjects">All Projects</a></li>
            <li><a href="#favorites">Favorites</a></li>
            <li><a href="#recentProjects">Recent</a></li>
        </ul>

        <div id="myProjects" class="scrollingTab">
            @include('menu::partials.projectListFilter', ['clients' => $clients, 'projectSelectFilter' => $projectSelectFilter])
            <ul class="selectorList projectList htmx-loaded-content">
                @if($projectSelectFilter["groupBy"] == "client")
                    @include('menu::partials.clientGroup', ['projects' => $allAssignedProjects, 'parent' => 0, 'level'=> 0, "prefix" => "myClientProjects"])
                @elseif($projectSelectFilter["groupBy"] == "structure")
                    @include('menu::partials.projectGroup', ['projects' => $projectHierarchy, 'parent' => 0, 'level'=> 0, "prefix" => "myProjects"])
                @else
                    @include('menu::partials.noGroup', ['projects' => $allAssignedProjects])
                @endif
            </ul>
        </div>
        <div id="allProjects" class="scrollingTab">
            @include('menu::partials.projectListFilter', ['clients' => $clients, 'projectSelectFilter' => $projectSelectFilter])
            <ul class="selectorList projectList htmx-loaded-content">
                @if($projectSelectFilter["groupBy"] == "client")
                    @include('menu::partials.clientGroup', ['projects' => $allAvailableProjects, 'parent' => 0, 'level'=> 0, "prefix" => "allClientProjects"])
                @elseif($projectSelectFilter["groupBy"] == "structure")
                    @include('menu::partials.projectGroup', ['projects' => $allAvailableProjectsHierarchy, 'parent' => 0, 'level'=> 0, "prefix" => "allProjects"])
                @else
                    @include('menu::partials.noGroup', ['projects' => $allAvailableProjects])
                @endif
            </ul>
        </div>
        <div id="recentProjects" class="scrollingTab">
            <ul class="selectorList projectList">
                @if(count($recentProjects) >= 1)
                    @include('menu::partials.noGroup', ['projects' => $recentProjects])
                @else
                    <li class='nav-header'></li>
                    <li><span class='info'>
                        {{ __("menu.you_dont_have_projects") }}
                        </span>
                    </li>
                @endif
            </ul>
        </div>
        <div id="favorites" class="scrollingTab">
            <ul class="selectorList projectList">
                @if(count($favoriteProjects) >= 1)
                    @include('menu::partials.noGroup', ['projects' => $favoriteProjects])
                @else
                    <li><span class='info'>
                        {{ __("text.you_have_not_favorited_any_projects") }}
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
    @if ($login::userIsAtLeast("manager"))
        <div class="projectSelectorFooter">
            <ul class="selectorList projectList">
                @dispatchEvent('beforeProjectCreateLink')
                <li><a href="{{ BASE_URL }}/projects/newProject">{!! __('menu.create_project') !!}</a></li>
                @dispatchEvent('afterProjectCreateLink')
            </ul>
        </div>
    @endif
</div>

<script>
    jQuery(document).ready(function () {
        leantime.menuController.initProjectSelector();
    });
</script>


