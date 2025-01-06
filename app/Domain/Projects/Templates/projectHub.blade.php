@extends($layout)

@section('content')

    @props([
        'includeTitle' => true,
        'allProjects' => []
    ])

    <div class="maincontent" style="margin-top:0px">
        <div style="padding:10px 0px">
            <div class="center">
                <span style="font-size:38px; color:var(--main-titles-color););">
                    {{ __("headline.project_hub") }}
                </span><br />
                <span style="font-size:18px; color:var(--main-titles-color););">
                   {{ __("text.project_hub_intro") }}
                    @if ($login::userIsAtLeast("manager"))
                        <br /><br />
                        <x-global::forms.button tag="a" content-role="secondary" href="#/projects/createnew">
                            {!! __("menu.create_something_new") !!}
                        </x-global::forms.button>
                    @endif
                </span>
                <br />
                <br />
            </div>
        </div>

        @if(is_array($allProjects) && count($allProjects) == 0)
            <x-global::elements.undrawSvg image="undraw_a_moment_to_relax_bbpa.svg" style="color:var(--main-titles-color);" maxWidth="30%">
            </x-global::elements.undrawSvg>

        @endif

        @include('projects::partials.projectHubProjects')

    </div>
@endsection

