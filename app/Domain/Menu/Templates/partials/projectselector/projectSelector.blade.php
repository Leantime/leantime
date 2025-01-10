<x-global::actions.dropdown
    class="bigProjectSelector {{ $menuType == 'project' ? 'active' : '' }}"
    content-role='ghost'
    variant="card"
    card-class="!p-0" >
    <x-slot:labelText>
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

        <i class="fa fa-caret-down" aria-hidden="true"></i>
    </x-slot:labelText>
    <x-slot:card-content class="">
        <div class="projectselector w-96 h-fit" id="mainProjectSelector">
            @include('menu::partials.projectselector.projectSelectorDropdown', [])
        </div>
    </x-slot:card-content>
</x-global::actions.dropdown>

<script type="module">
    jQuery(document).ready(function () {
        jQuery(document).on('click', '.projectselector.dropdown-menu', function (e) {
            e.stopPropagation();
        });
    });
</script>
