@extends($layout)

@section('content')

    <x-global::pageheader :icon="'fa fa-puzzle-piece'">
        <h1>My Apps</h1>
    </x-global::pageheader>

    @displayNotification()

    <div class="maincontent">

        @include('plugins::partials.plugintabs',  ["currentUrl" => "installed"])

        <div class="maincontentinner">

            <div class="row">
                <div class="col-lg-12">
                    <h5 class="subtitle">
                        {{ __("text.installed_plugins") }}
                    </h5>
                    <div class="row sortableTicketList">
                        @each('plugins::partials.plugin', $tpl->get("installedPlugins"), 'plugin')


                        @if ($tpl->get("installedPlugins") === false || count($tpl->get("installedPlugins")) == 0)
                                <span class="tw-block tw-px-4 tw-mb-4">{{ __("text.no_plugins_activated") }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <h5 class="subtitle">
                        {{ __("text.new_plugins") }}
                    </h5>
                    <ul class="sortableTicketList" >
                        @if (count($tpl->get("newPlugins")) > 0)
                            @foreach ($tpl->get("newPlugins") as $newplugin)
                                <li>
                                    <div class="ticketBox fixed">
                                        <div class="row">

                                            <div class="col-md-4">
                                                <strong>{{ $newplugin->name }}<br /></strong>
                                            </div>
                                            <div class="col-md-4">
                                                {{ $newplugin->description }}<br />
                                                {{ $tpl->__("text.version") }} {{ $newplugin->version }}
                                                @if (is_array($newplugin->authors) && count($newplugin->authors) > 0)
                                                    | {{ $tpl->__("text.by") }} <a href="mailto:{{ $newplugin->authors[0]["email"] }}">{{ $newplugin->authors[0]["name"] }}</a>
                                                @endif
                                               | <a href="{{ $newplugin->homepage }}"> {{ $tpl->__("text.visit_site") }} </a>
                                            </div>
                                            <div class="col-md-4" style="padding-top:5px;">
                                                <a href="{{ BASE_URL }}/plugins/myapps?install={{ $newplugin->foldername }}" class="btn btn-default pull-right">{{ $tpl->__('buttons.activate') }}</a>

                                            </div>

                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <x-global::undrawSvg image="undraw_empty_cart_co35.svg" headline="Nothing New">
                                We couldn't discover any new plugins in your plugin folder, please make sure the plugin is unzipped and contains a composer.json file.
                            </x-global::undrawSvg>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

