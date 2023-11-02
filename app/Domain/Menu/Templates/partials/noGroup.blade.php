<ul class="level-0 noGroup">
    @foreach($projects as $project)

        @if(
           !isset($_SESSION['userdata']["projectSelectFilter"]['client'])
           || $_SESSION['userdata']["projectSelectFilter"]['client'] == $project["clientId"]
           || $_SESSION['userdata']["projectSelectFilter"]['client'] == 0
           || $_SESSION['userdata']["projectSelectFilter"]['client'] == ""
           )

            <li class="projectLineItem hasSubtitle {{ $_SESSION['currentProject'] ?? 0  == $project['id'] ? "active" : '' }}" >
                @include('menu::partials.projectLink')
                <div class="clear"></div>
            </li>

        @endif

    @endforeach
</ul>
