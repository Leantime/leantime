<ul class="level-0 noGroup">
    @foreach($projects as $project)

        @if(
           !session()->exists("userdata.projectSelectFilter.client")
            || session("userdata.projectSelectFilter.client") == $project["clientId"]
            || session("userdata.projectSelectFilter.client") == 0
            || session("userdata.projectSelectFilter.client") == ""
           )

            <li class="projectLineItem hasSubtitle {{ session("currentProject") ?? 0  == $project['id'] ? "active" : '' }}" >
                @include('menu::partials.projectLink')
                <div class="clear"></div>
            </li>

        @endif

    @endforeach
</ul>
