<ul id="group-{{ $parent }}" class="level-{{ $level }} projectGroup">
    @foreach($projects as $project)
        <li class="projectLineItem hasSubtitle">
            @if(!empty($project['children']) && count($project['children']) >0)
                <a href="" class="toggler" onclick="leantime.menuController.toggleClientList('{{ $project["id"] }}', this)">
                    <i class="fa fa-chevron-down"></i>
                </a>
            @endif

            @include('menu::partials.projectLink')

            <div class="clear"></div>

            @if(!empty($project['children']) && count($project['children']) >0)
                @include('menu::partials.projectGroup', ['projects' => $project['children'], 'parent' => $project['id'], 'level'=> $level+1])
            @endif
        </li>
    @endforeach
</ul>
