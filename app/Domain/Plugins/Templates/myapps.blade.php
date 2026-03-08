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
                <h5 class="subtitle tw:mb-4">
                    {{ __("text.installed_plugins") }}
                </h5>
                <div>
                    @each('plugins::partials.plugin', $tpl->get("installedPlugins"), 'plugin')

                    @if ($tpl->get("installedPlugins") === false || count($tpl->get("installedPlugins")) == 0)
                        <span class="tw:block tw:px-4 tw:mb-4">{{ __("text.no_plugins_activated") }}</span>
                    @endif
                </div>
            </div>

            <div class="tw:mt-8">
                <h5 class="subtitle tw:mb-4">
                    {{ __("text.new_plugins") }}
                </h5>
                <div>
                    @if (count($tpl->get("newPlugins")) > 0)
                        @foreach ($tpl->get("newPlugins") as $newplugin)
                            <div class="tw:bg-[var(--kanban-card-bg)] tw:rounded-[var(--box-radius)] tw:shadow-[var(--regular-shadow)] tw:p-5 tw:mb-3">
                                <div class="tw:flex tw:items-center tw:gap-5">
                                    <div class="tw:flex-1 tw:min-w-0">
                                        <strong>{{ $newplugin->name }}</strong>
                                        @if (!empty($newplugin->version))
                                            <span class="tw:text-[color:var(--secondary-font-color)]">v{{ $newplugin->version }}</span>
                                        @endif

                                        <div class="tw:mt-1 tw:text-[color:var(--secondary-font-color)]">
                                            @if (is_array($newplugin->authors) && count($newplugin->authors) > 0)
                                                {{ $tpl->__("text.by") }} <a href="mailto:{{ $newplugin->authors[0]["email"] }}">{{ $newplugin->authors[0]["name"] }}</a>
                                            @endif
                                            @if (!empty($newplugin->homepage))
                                                @if (is_array($newplugin->authors) && count($newplugin->authors) > 0) &middot; @endif
                                                <a href="{{ $newplugin->homepage }}">{{ $tpl->__("text.visit_site") }}</a>
                                            @endif
                                        </div>

                                        @if (!empty($newplugin->description))
                                            <p class="tw:mt-2 tw:text-[color:var(--primary-font-color)] tw:leading-normal">
                                                {{ $newplugin->description }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="tw:shrink-0">
                                        <x-globals::forms.button link="{{ BASE_URL }}/plugins/myapps?install={{ $newplugin->foldername }}" contentRole="secondary">{{ $tpl->__('buttons.activate') }}</x-globals::forms.button>
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
