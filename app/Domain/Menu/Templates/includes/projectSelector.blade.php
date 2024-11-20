<div class="dropdown dropdown-bottom dropdown-start">
    <div tabindex="0" role="button" class="btn btn-ghost bigProjectSelector dropdown-toggle" >
        @if ($menuType == 'project' || $menuType == 'default')
            <div class="w-8 h-8 rounded bg-gradient-to-r from-primary to-primary-focus flex items-center justify-center">
                @if (isset($projectTypeAvatars[$currentProjectType]) && $projectTypeAvatars[$currentProjectType] != 'avatar')
                    <span class="{{ $projectTypeAvatars[$currentProjectType] }}"></span>
                @else
                    <img class="w-full h-full rounded object-cover"
                        src="{{ BASE_URL }}/api/projects?projectAvatar={{ $currentProject['id'] ?? -1 }}&v={{ format($currentProject['modified'] ?? '')->timestamp() }}" 
                        alt="Project Avatar" />
                @endif
            </div>
            <span>{{ $currentProject['name'] ?? '' }}</span>
        @else
            <span class="text-gray-600">{!! __('menu.projects') !!}</span>
        @endif

        <i class="fa fa-caret-down"></i>
    </div>

    @include('menu::includes.projectSelectorDropdown', [])
</div>
