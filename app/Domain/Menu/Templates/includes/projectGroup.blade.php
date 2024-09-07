@php
    $groupState = session("usersettings.submenuToggle.".$prefix.'-projectSelectorlist-group-'.$parent, 'closed');
@endphp
<ul id="{{ $prefix }}-projectSelectorlist-group-{{ $parent }}" class="level-{{ $level }} projectGroup {{ $groupState }}" hx-boost="true" hx-indicator="#global-loader">
    @foreach($projects as $project)

        @if(
            !session()->exists("usersettings.projectSelectFilter.client")
            || session("usersettings.projectSelectFilter.client") == $project["clientId"]
            || session("usersettings.projectSelectFilter.client") == 0
            || session("usersettings.projectSelectFilter.client") == ""
            || $project["clientId"] == ''
            )

            <li class="projectLineItem hasSubtitle {{ session("currentProject") == $project['id'] ? "active" : '' }}" >
                @php
                    $parentState = session("usersettings.submenuToggle.".$prefix.'-projectSelectorlist-group-'.$project['id'], 'closed');
                @endphp

                @if((empty($project['children']) || count($project['children']) ==0))
                    <span class="toggler"></span>
                @endif

                @if(!empty($project['children']) && count($project['children']) >0)
                    <a href="javascript:void(0);" class="toggler {{ $parentState }}" id="{{ $prefix }}-toggler-{{ $project["id"] }}" onclick="leantime.menuController.toggleProjectDropDownList('{{ $project["id"] }}', '', '{{ $prefix }}')">
                        @if($parentState == 'closed')
                            <i class="fa fa-angle-right"></i>
                        @else
                            <i class="fa fa-angle-down"></i>
                        @endif
                    </a>
                @endif
                @include('menu::includes.projectLink')

                <div class="clear"></div>

                @if(!empty($project['children']) && count($project['children']) >0)
                    @include('menu::partials.projectGroup', ['projects' => $project['children'], 'parent' => $project['id'], 'level'=> $level+1, 'prefx' => $prefix, "currentProject"=>$currentProject])
                @endif
            </li>

        @endif
    @endforeach
</ul>
