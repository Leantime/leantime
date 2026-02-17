<div class="tw:text-center padding-lg" style="max-width:1200px;">

    <div>
        <h1 style="font-size:var(--font-size-xxxl);">Create something new</h1><br />
        {!!  __("text.creation_hub") !!}

        <br />
        <br />
    </div>


    <div class="tw:grid tw:grid-cols-3 tw:gap-6">

        @foreach($projectTypes as $projectType)
        <div class="{{ $projectType["active"] !== true ? "disabled" : "" }}"  >
            <div class="profileBox">

                <x-global::undrawSvg image="{{ $projectType['image'] }}" headline="{{  __($projectType['label'])  }}" maxWidth="50%" height="150px"></x-global::undrawSvg>

                <br />
                {!! __($projectType["description"]) !!}
                <br /><br />
                @if($projectType["active"] == true )
                    <a href="{{ BASE_URL }}/{{$projectType["url"] }}" class="btn btn-primary {{ $projectType["active"] !== true ? "disabled" : "" }}">{{  __($projectType['btnLabel'])  }}</a>
                @else
                    <a href="#" class="btn btn-primary disabled">Not Available in this plan</a>
                @endif
                <div class="clearall"></div>

            </div>

        </div>
        @endforeach

    </div>


</div>
