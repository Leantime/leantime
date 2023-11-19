
<a href="javascript:void(0);"
   class="dropdown-toggle bigProjectSelector {{ $menuType == "project" ? "active" : "" }}"
   data-toggle="dropdown">
    <span class="projectAvatar {{ $currentProjectType }}">
        @if(isset($projectTypeAvatars[$currentProjectType]) && $projectTypeAvatars[$currentProjectType] != "avatar")
            <span class="{{ $projectTypeAvatars[$currentProjectType] }}"></span>
        @else
            <img src="{{ BASE_URL }}/api/projects?projectAvatar={{ $currentProject['id'] ?? -1 }}&v={{ strtotime($currentProject['modified'] ?? '0') }}"/>
        @endif
    </span>
    {{ $currentProject['name'] ?? "" }}&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
</a>
@include('menu::partials.projectSelector', [])
