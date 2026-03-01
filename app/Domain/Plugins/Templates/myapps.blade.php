@extends($layout)

@section('content')

    <x-globals::layout.page-header :icon="'extension'">
        <h1>My Apps</h1>
    </x-globals::layout.page-header>

    @displayNotification()

    <div class="maincontent">

        @include('plugins::partials.plugintabs',  ["currentUrl" => "installed"])

        <div class="maincontentinner">

            <div>
                <h5 class="subtitle" style="margin-bottom: 15px;">
                    {{ __("text.installed_plugins") }}
                </h5>
                <div>
                    @each('plugins::partials.plugin', $tpl->get("installedPlugins"), 'plugin')

                    @if ($tpl->get("installedPlugins") === false || count($tpl->get("installedPlugins")) == 0)
                        <span class="tw:block tw:px-4 tw:mb-4">{{ __("text.no_plugins_activated") }}</span>
                    @endif
                </div>
            </div>

            <div style="margin-top: 30px;">
                <h5 class="subtitle" style="margin-bottom: 15px;">
                    {{ __("text.new_plugins") }}
                </h5>
                <div>
                    @if (count($tpl->get("newPlugins")) > 0)
                        @foreach ($tpl->get("newPlugins") as $newplugin)
                            <div style="background: var(--kanban-card-bg); border-radius: var(--box-radius); box-shadow: var(--regular-shadow); padding: 20px; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 20px;">
                                    <div style="flex: 1; min-width: 0;">
                                        <strong style="font-size: var(--font-size-l);">{{ $newplugin->name }}</strong>
                                        @if (!empty($newplugin->version))
                                            <span style="color: var(--secondary-font-color); font-size: var(--font-size-s);">v{{ $newplugin->version }}</span>
                                        @endif

                                        <div style="margin-top: 4px; font-size: var(--font-size-s); color: var(--secondary-font-color);">
                                            @if (is_array($newplugin->authors) && count($newplugin->authors) > 0)
                                                {{ $tpl->__("text.by") }} <a href="mailto:{{ $newplugin->authors[0]["email"] }}">{{ $newplugin->authors[0]["name"] }}</a>
                                            @endif
                                            @if (!empty($newplugin->homepage))
                                                @if (is_array($newplugin->authors) && count($newplugin->authors) > 0) &middot; @endif
                                                <a href="{{ $newplugin->homepage }}">{{ $tpl->__("text.visit_site") }}</a>
                                            @endif
                                        </div>

                                        @if (!empty($newplugin->description))
                                            <p style="margin: 8px 0 0; font-size: var(--font-size-s); color: var(--primary-font-color); line-height: 1.5;">
                                                {{ $newplugin->description }}
                                            </p>
                                        @endif
                                    </div>

                                    <div style="flex-shrink: 0;">
                                        <x-globals::forms.button link="{{ BASE_URL }}/plugins/myapps?install={{ $newplugin->foldername }}" type="secondary">{{ $tpl->__('buttons.activate') }}</x-globals::forms.button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <x-globals::undrawSvg image="undraw_empty_cart_co35.svg" headline="Nothing New">
                            We couldn't discover any new plugins in your plugin folder, please make sure the plugin is unzipped and contains a composer.json file.
                        </x-globals::undrawSvg>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
