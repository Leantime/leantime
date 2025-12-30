<a href='{{ BASE_URL }}/projects/changeCurrentProject/{{ $project["id"] }}'
   @if(strlen($project["name"]) > 25)
       data-tippy-content='{{ $project["name"] }}'
    @endif >
    <span class='projectAvatar' >
        @if(isset($projectTypeAvatars[$project["type"]]) && $projectTypeAvatars[$project["type"]] != "avatar")
            <span class="{{ $projectTypeAvatars[$project["type"]] }}"></span>
        @else
            <img src='{{ BASE_URL }}/api/projects?projectAvatar={{ $project["id"] }}&v={{  format($project['modified'])->timestamp() }}' style = "border-radius:5px;"/>
        @endif
    </span>
    <span class='projectName'>
        @if($project["clientName"] != '')
            <small>{{ $project["clientName"] }}</small><br />
        @else
            <small>{{ __('projectType.'.$project["type"] ?? 'project') }}</small><br />
        @endif

        {{ $project["name"] }}
    </span>
</a>
