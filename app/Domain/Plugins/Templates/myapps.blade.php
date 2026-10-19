@extends($layout)

@section('content')

    <x-global::layout.page-header icon="fa fa-puzzle-piece">
        {{ __('headlines.my_apps') }}
    </x-global::layout.page-header>

    @displayNotification()

    <div class="maincontent">

        @include('plugins::partials.plugintabs',  ["currentUrl" => "installed"])

        <x-global::layout.content-box>

            <div class="row">
                <div class="col-lg-12">
                    <x-global::elements.section-header as="h5" style="margin-bottom:15px;">
                        {{ __("text.installed_plugins") }}
                        <x-slot:actions>
                            <x-global::forms.button tag="a" link="javascript:void(0);" scale="s" contentRole="secondary"
                                data-post-url="{{ BASE_URL }}/plugins/myapps" data-post-field="checkUpdates" data-post-value="1"
                                leadingVisual="fa fa-rotate">{{ __('buttons.check_plugin_updates') }}</x-global::forms.button>
                        </x-slot:actions>
                    </x-global::elements.section-header>
                    {{-- Off by default. Applies minor and patch releases of marketplace plugins in the daily check;
                         each update is verified and rolled back if it doesn't load. --}}
                    <form method="post" action="{{ BASE_URL }}/plugins/myapps" class="tw-mb-m">
                        <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                        <input type="hidden" name="autoUpdateShown" value="1" />
                        <x-global::forms.checkbox variant="toggle" name="autoUpdate" id="pluginAutoUpdate" :checked="$autoUpdate"
                            :description="__('text.plugin_auto_update_hint')" onchange="this.form.submit();">{{ __('label.plugin_auto_update') }}</x-global::forms.checkbox>
                    </form>
                    <div class="row sortableTicketList">
                        @each('plugins::partials.plugin', $installedPlugins, 'plugin')

                        @if ($installedPlugins === false || count($installedPlugins) == 0)
                            <span class="tw-block tw-px-4 tw-mb-4">{{ __("text.no_plugins_activated") }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-lg-12">
                    <x-global::elements.section-header as="h5" class="tw-mb-m" style="margin-bottom:15px;">
                        {{ __("text.new_plugins") }}
                    </x-global::elements.section-header>
                    <ul class="sortableTicketList" >
                        @if (count($newPlugins) > 0)
                            @foreach ($newPlugins as $newplugin)
                                <li>
                                    <x-global::elements.card compact class="ticketBox fixed">
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
                                                <x-global::forms.button tag="a" link="javascript:void(0);" data-post-url="{{ BASE_URL }}/plugins/myapps" data-post-field="install" data-post-value="{{ $newplugin->foldername }}" contentRole="default" class="pull-right">{{ $tpl->__('buttons.activate') }}</x-global::forms.button>

                                            </div>

                                        </div>
                                    </x-global::elements.card>
                                </li>
                            @endforeach
                        @else
                            <x-global::elements.empty-state image="undraw_no_data_re_kwbl.svg" titleAs="h3" :title="__('headlines.no_new_plugins')"
                                :description="__('text.no_new_plugins')" />
                        @endif
                    </ul>
                </div>
            </div>
        </x-global::layout.content-box>
    </div>
@endsection

