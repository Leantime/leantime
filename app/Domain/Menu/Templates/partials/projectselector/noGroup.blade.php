<ul class="level-0 noGroup" hx-boost="true" hx-indicator="#global-loader">
    @foreach($projects as $project)

        @if(
           !session()->exists("usersettings.projectSelectFilter.client")
            || session("usersettings.projectSelectFilter.client") == $project["clientId"]
            || session("usersettings.projectSelectFilter.client") == 0
            || session("usersettings.projectSelectFilter.client") == ""
           )

            <li class="projectLineItem hasSubtitle {{ session("currentProject") ?? 0  == $project['id'] ? "active" : '' }}" >
                <x-projects::projectCard :project="$project" variant="compact"></x-projects::projectCard>
                <div class="clear"></div>
            </li>

        @endif

    @endforeach
</ul>
