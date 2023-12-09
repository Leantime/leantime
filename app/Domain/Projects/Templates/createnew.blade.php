<div class="center padding-lg" style="max-width:1200px;">

    <div class="row">
        <div class="col-md-12">
            <h1 style="font-size:var(--font-size-xxxl);">Create something new</h1><br />

            <br />
            <br />
        </div>
    </div>


    <div class="row">

        @foreach($projectTypes as $projectType)
        <div class="col-md-4 {{ $projectType["active"] !== true ? "disabled" : "" }}"  >
            <div class="profileBox">


                <x-global::undrawSvg image="{{ $projectType['image'] }}" headline="">
                </x-global::undrawSvg>


                <span class="userName">
                    <a href="{{ BASE_URL }}/{{$projectType["url"] }}" target="_blank">
                        <strong>Create {{ __($projectType["label"]) }}</strong>
                    </a>
                </span>

                {{ __($projectType["description"]) }}
                <br /><br />
                <a href="{{ BASE_URL }}/{{$projectType["url"] }}" class="btn btn-primary {{ $projectType["active"] !== true ? "disabled" : "" }}">Start Now</a>
                <div class="clearall"></div>

            </div>
            
        </div>
        @endforeach

    </div>


</div>
