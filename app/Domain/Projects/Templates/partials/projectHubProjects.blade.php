<div id="myProjectsHub"
     hx-get="{{BASE_URL}}/hx/projects/projectHubProjects/get"
     hx-trigger="HTMX.updateProjectList from:body"
     hx-target="#myProjectsHub"
     hx-swap="outerHTML">

    @if (count($clients) > 0)
        <div class="dropdown dropdownWrapper pull-right">
            <form  hx-get="{{BASE_URL}}/hx/projects/projectHubProjects/get"
                   hx-trigger="submit, change"
                   hx-target="#myProjectsHub"
                   hx-swap="outerHTML transition:true">
                <x-global::forms.select name="client">
                    <x-global::forms.select.option value="" :selected="$currentClient == ''">
                        All Clients
                    </x-global::forms.select.option>
                    @foreach ($clients as $key => $value)
                        <x-global::forms.select.option value="{{ $key }}" :selected="($currentClient == $key)">
                            {{ $value['name'] }}
                        </x-global::forms.select.option>
                    @endforeach
                </x-global::forms.select>
            </form>
        </div>
    @endif

    @if (count($allProjects) == 0)
        <br /><br />
        <div class='center'>
            <div style='width:70%; color:var(--main-titles-color)' class='svgContainer'>
                {{ __('notifications.not_assigned_to_any_project') }}
                @if($login::userIsAtLeast($roles::$manager))
                    <br /><br />
                    <a href='{{ BASE_URL }}/projects/newProject' class='btn btn-primary'>{{ __('link.new_project') }}</a>
                @endif
            </div>
        </div>
    @endif

    <x-global::content.accordion id="myProjectsHub-favorites" class="noBackground" light="true">
        <x-slot name="title">⭐ My Favorites</x-slot>
        <x-slot name="content">
            <div class="row">
                @php
                    $hasFavorites = false;
                @endphp
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == true)
                        <div class="col-md-4">
                            <x-projects::projectCard :project="$project" variant="full"></x-projects::projectCard>
                        </div>
                        @php
                            $hasFavorites = true;
                        @endphp
                    @endif
                @endforeach
                @if($hasFavorites === false)
                    <div class="text-primary-content col-md-12">
                        {{ __("text.no_favorites") }}
                    </div>
                @endif
            </div>
        </x-slot>
    </x-global::content.accordion>

    <x-global::content.accordion id="myProjectsHub-otherProjects" class="noBackground" light="true">
        <x-slot name="title">
            {{ __("text.all_assigned_projects")  }}
        </x-slot>
        <x-slot name="content">
            <div class="row">
                @foreach ($allProjects as $project)
                    @if($project['isFavorite'] == false)
                        <div class="col-md-3">
                            <x-projects::projectCard :project="$project" variant="full"></x-projects::projectCard>
                        </div>

                    @endif
                @endforeach
            </div>
        </x-slot>
    </x-global::content.accordion>
</div>
