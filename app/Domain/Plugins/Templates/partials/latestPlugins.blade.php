
<h1>Latest Plugin Updates</h1>
<p>Extend Leantime using our latest plugins.<br /><a href="{{ BASE_URL }}/plugins/marketplace"><i class="fa fa-cogs"></i> Manage Your Apps</a></p><br />

<br />
<div>
    <ul>

@foreach($plugins as $plugin)

    <li onclick="window.location='#/plugins/details/{{ $plugin->identifier }}'">
        <img src="{{ $plugin->getPluginImageData() }}" width="75" height="75" class="tw-rounded tw-float-left tw-mr-m"/>
            @if (! empty($plugin->name))
            <a href="#/plugins/details/{{ $plugin->identifier }}"> <strong>{!! $plugin->name !!}</strong> {{ $plugin->version ? "(v".$plugin->version.")" : "" }}</a><br />
                <x-global::inlineLinks :links="$plugin->getMetadataLinks()" />
            @endif

            @if (! empty($desc = $plugin->getCardDesc()))
                <p>{!! $desc !!}</p>
            @endif
        <div class="clearall"></div>
        <hr />

{{--        <div class="row tw-mb-base">--}}
{{--            <div class="col tw-flex tw-flex-col tw-gap-base">--}}


{{--                <div class="tw-flex tw-flex-row tw-gap-base">--}}
{{--                    <div class="plugin-price tw-flex-1 tw-content-center" >--}}
{{--                        <strong>{!! $plugin->getPrice() !!}</strong><br />--}}
{{--                    </div>--}}
{{--                    <div class="tw-border-t tw-border-[var(--main-border-color)] tw-px-base tw-text-right tw-flex-1 tw-justify-items-end">--}}
{{--                        @include($plugin->getControlsView(), ["plugin" => $plugin])--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
    </li>

@endforeach
    </ul>
