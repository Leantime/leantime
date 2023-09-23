@props([
    'redirect' => 'dashboard/show'
])

<div class="dropdown-menu projectselector">
    <div class="head">
        <span class="sub">{{ __("menu.current_project") }}</span><br />
        <span class="title">{{ $_SESSION['currentProjectName'] }}</span>
    </div>
    <div class="tabbedwidget tab-primary projectSelectorTabs">
        <ul class="tabs">
            <li><a href="#allProjects">All</a></li>
            <li><a href="#recentProjects">Recent</a></li>
            <li><a href="#favoriteProjects">Favorites</a></li>
        </ul>
        <div id="allProjects" class="scrollingTab">
            <div class="row">
                <div class="col-md-12">
                    <ul class="selectorList projectList">
                        @include('menu::partials.projectGroup', ['projects' => $projectHierarchy, 'parent' => 0, 'level'=> 0])
                    </ul>
                </div>
            </div>
        </div>
        <div id="recentProjects" class="scrollingTab">
            <ul class="selectorList clientList">
                @if(count($recentProjects) >= 1)
                    @foreach($recentProjects as $project)
                        <li class='projectLineItem hasSubtitle {{ $currentProject == $project["id"] ? "active" : ""}}">
                            @include('menu::partials.projectLink')
                        </li>
                    @endforeach
                @else
                    <li class='nav-header'></li>
                    <li><span class='info'>
                        {{ __("menu.you_dont_have_projects") }}
                        </span>
                    </li>
                @endif
            </ul>
        </div>
        <div id="favoriteProjects" class="scrollingTab">
            <ul class="selectorList clientList">

                @if(count($allAvailableProjects) >= 1)
                    @foreach($allAvailableProjects as $project)
                        @if($project['isFavorite'])
                            <li class='projectLineItem hasSubtitle {{ $currentProject == $project["id"] ? "active" : ""}}">
                               @include('menu::partials.projectLink')
                            </li>
                        @endif
                    @endforeach
                @else
                    <li class='nav-header'></li>
                    <li><span class='info'>
                        {{ __("menu.you_dont_have_projects") }}
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
