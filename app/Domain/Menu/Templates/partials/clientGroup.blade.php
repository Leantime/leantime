
@php
    $lastClient = '';
@endphp

<ul id="{{ $prefix }}-projectSelectorlist-group-{{ $parent }}" class="level-{{ $level }} projectGroup">
    @foreach($projects as $project)

        @php
            $parentState = session("usersettings.submenuToggle.".$prefix.'-projectSelectorlist-group-'.$project['clientId'], 'closed');
        @endphp

        @if(
           !session()->exists("usersettings.projectSelectFilter.client")
            || session("usersettings.projectSelectFilter.client") == $project["clientId"]
            || session("usersettings.projectSelectFilter.client") == 0
            || session("usersettings.projectSelectFilter.client") == ""
           )

            @if ($lastClient != $project['clientName'])

                @php
                    $lastClient = $project['clientName']
                @endphp

                @if(!$loop->first)
                    </ul>
                @endif

                <li class='projectLineItem clientIdHead-{{$project['clientId'] }}'>
                    <a href="javascript:void(0);"
                        class="toggler {{ $parentState }}"
                        id="{{ $prefix }}-toggler-{{ $project["clientId"] }}"
                        onclick="leantime.menuController.toggleProjectDropDownList('{{ $project["clientId"] }}', '', '{{ $prefix }}')">
                    @if($parentState == 'closed')
                        <i class="fa fa-angle-right"></i>
                    @else
                        <i class="fa fa-angle-down"></i>
                    @endif
                    </a>
                    <a href="javascript:void(0)">
                        {{ $project['clientName'] }}
                    </a>
                    <ul id="{{ $prefix }}-projectSelectorlist-group-{{ $project['clientId'] }}" class="level-1 projectGroup {{ $parentState }}">
            @endif

            <li class="projectLineItem hasSubtitle {{ session("currentProject") == $project['id'] ? "active" : '' }}" >
                @include('menu::partials.projectLink')
                <div class="clear"></div>
            </li>
        @endif
    @endforeach
</ul>
