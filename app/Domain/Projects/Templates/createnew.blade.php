<div class="center padding-lg" style="max-width:1200px;">

    <div>
        <h1 style="font-size:var(--font-size-xxxl);">Create something new</h1><br />
        {!!  __("text.creation_hub") !!}

        <br />
        <br />
    </div>


    <div class="row">

        @foreach($projectTypes as $projectType)
        <div class="col-md-4 {{ $projectType["active"] !== true ? "disabled" : "" }}"  >
            <div class="profileBox">

                <x-global::undrawSvg image="{{ $projectType['image'] }}" headline="{{  __($projectType['label'])  }}" maxWidth="50%" height="150px"></x-global::undrawSvg>

                <br />
                {!! __($projectType["description"]) !!}
                <br /><br />
                @if($projectType["active"] == true )
                    <x-global::button link="{{ BASE_URL }}/{{$projectType['url'] }}" type="primary" :disabled="$projectType['active'] !== true">{{  __($projectType['btnLabel'])  }}</x-global::button>
                @else
                    <x-global::button link="#" type="primary" :disabled="true">Not Available in this plan</x-global::button>
                @endif
                <div class="clearall"></div>

            </div>

        </div>
        @endforeach

    </div>


</div>
