<a href='{{ BASE_URL }}/projects/changeCurrentProject/{{ $project["id"] }}?redirect={{ $redirectUrl }}'
    @if(strlen($project["name"]) > 25)
        data-tippy-content='{{ $project["name"] }}'
    @endif >
    <span class='projectAvatar'>
        @if(isset($projectTypeAvatars[$project["type"]]) && $projectTypeAvatars[$project["type"]] != "avatar")
            <span class="{{ $projectTypeAvatars[$project["type"]] }}"></span>
        @else
            <img src='{{ BASE_URL }}/api/projects?projectAvatar={{ $project["id"] }}' />
        @endif
    </span>
    <span class='projectName'>
        <small>{{ $project["clientName"] }}</small><br />
        {{ $project["name"] }}
    </span>
</a>
