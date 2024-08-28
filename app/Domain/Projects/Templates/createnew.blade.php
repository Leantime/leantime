<div class="center padding-lg" style="max-width:1200px;">

    <div class="row">
        <div class="col-md-12">
            <h1 style="font-size:var(--font-size-xxxl);">Create something new</h1><br />
            {!!  __("text.creation_hub") !!}

            <br />
            <br />
        </div>
    </div>


    <div class="row">

        @foreach($projectTypes as $projectType)
        <div class="col-md-4 {{ $projectType["active"] !== true ? "disabled" : "" }}"  >
            <div class="profileBox">

                <x-global::elements.undrawSvg image="{{ $projectType['image'] }}" headline="{{  __($projectType['label'])  }}" maxWidth="50%" height="150px"></x-global::elements.undrawSvg>

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
