@extends($layout)

@section('content')

    <x-globals::layout.page-header :icon="'fa fa-puzzle-piece'">
        <h1>My Apps</h1>
    </x-globals::layout.page-header>

    @displayNotification()

    <div class="maincontent">

        @include('plugins::partials.plugintabs',  ["currentUrl" => "installed"])

        <div class="maincontentinner">

            <div>
                    <h5 class="subtitle" style="margin-bottom:15px;">
                        {{ __("text.installed_plugins") }}
                    </h5>
                    <div class="row sortableTicketList">
                        @each('plugins::partials.plugin', $tpl->get("installedPlugins"), 'plugin')

                        @if ($tpl->get("installedPlugins") === false || count($tpl->get("installedPlugins")) == 0)
                            <span class="tw:block tw:px-4 tw:mb-4">{{ __("text.no_plugins_activated") }}</span>
                        @endif
                    </div>
            </div>
            <br />
            <div>
                    <h5 class="subtitle tw:mb-m" style="margin-bottom:15px;">
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
                                                <x-globals::forms.button link="{{ BASE_URL }}/plugins/myapps?install={{ $newplugin->foldername }}" type="secondary" class="pull-right">{{ $tpl->__('buttons.activate') }}</x-globals::forms.button>

                                            </div>

                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <x-globals::undrawSvg image="undraw_empty_cart_co35.svg" headline="Nothing New">
                                We couldn't discover any new plugins in your plugin folder, please make sure the plugin is unzipped and contains a composer.json file.
                            </x-globals::undrawSvg>
                        @endif
                    </ul>
            </div>
        </div>
    </div>
@endsection

