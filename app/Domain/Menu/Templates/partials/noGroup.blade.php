<ul class="level-0 noGroup">
    @foreach($projects as $project)

        @if(
           !session()->exists("usersettings.projectSelectFilter.client")
            || session("usersettings.projectSelectFilter.client") == $project["clientId"]
            || session("usersettings.projectSelectFilter.client") == 0
            || session("usersettings.projectSelectFilter.client") == ""
           )

            <li class="projectLineItem hasSubtitle {{ session("currentProject") ?? 0  == $project['id'] ? "active" : '' }}" >
                @include('menu::partials.projectLink')
                <div class="tw:clear-both"></div>
            </li>

        @endif

    @endforeach
</ul>
