<a href='{{ BASE_URL }}/projects/changeCurrentProject/{{ $project["id"] }}'
   @if(strlen($project["name"]) > 25)
       data-tippy-content='{{ $project["name"] }}'
    @endif >
    <span class='projectAvatar'>
       <span class="projectAvatar-text">{{ substr($project["name"], 0, 2) }}</span>
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
