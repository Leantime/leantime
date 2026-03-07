<a href="{{ BASE_URL }}/projects/showMy"
   class="dropdown-toggle bigProjectSelector {{ $menuType == "project" ? "active" : "" }}"
   data-toggle="dropdown">

    @if ($menuType == 'project' || $menuType == 'default')
        <span class="projectAvatar {{ $currentProjectType }}">
        @if(isset($projectTypeAvatars[$currentProjectType]) && $projectTypeAvatars[$currentProjectType] != "avatar")
                <span class="{{ $projectTypeAvatars[$currentProjectType] }}"></span>
            @else
                <img src="{{ BASE_URL }}/api/projects?projectAvatar={{ $currentProject['id'] ?? -1 }}&v={{ format($currentProject['modified'] ?? '')->timestamp() }}"/>
            @endif
        </span>
        {{ $currentProject['name'] ?? "" }}&nbsp;

    @else
        {!! __('menu.projects') !!}
    @endif

   <x-global::elements.icon name="arrow_drop_down" />
</a>
@include('menu::partials.projectSelector', [])
