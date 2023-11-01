@extends($layout)

@section('content')
    <x-global::tabs>
        <x-slot:headings>
            @foreach ($versions as $plugin)
                <x-global::tabs.heading :name="str()->camel($plugin->version)">{{ $plugin->version }}</x-global::tabs.heading>
            @endforeach
        </x-slot:headings>

        <x-slot:contents class="tw-flex tw-flex-col tw-gap-4">
            @foreach ($versions as $plugin)
                <x-global::tabs.content :name="str()->camel($plugin->version)">
                    @if (! empty($plugin->thumbnailUrl))
                        <img src="{{ $plugin->thumbnailUrl }}" alt="{{ $plugin->thumbnailUrl }}" class="tw-w-full tw-h-auto tw-mb-4">
                    @endif

                    @if (! empty($plugin->name))
                        <h2 class="tw-text-2xl">{{ $plugin->name }}</h2>
                    @endif

                    @if (! empty($plugin->description))
                        <p>{{ $plugin->description }}</p>
                    @endif

                    <div class="tw-flex tw-justify-between tw-items-center">
                        @if (! empty($plugin->marketplaceUrl))
                            <x-global::button
                                :link="$plugin->marketplaceUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                            >Get a license</x-global::button>
                        @else
                            <span>This plugin currently isn't available for purchase.</span>
                        @endif

                        @fragment('plugin-installation')
                            @if (! empty($plugin->marketplaceId))
                                @if (isset($formNotification) && ! empty($formNotification))
                                    <div class="tw-text-red-500">{!! $formNotification !!}</div>
                                @else
                                    <form
                                        class="tw-flex tw-gap-2 tw-items-center"
                                        hx-post="{{ BASE_URL }}/hx/plugins/details/install"
                                        hx-swap="outerHTML"
                                        hx-indicator=".htmx-indicator, .htmx-loaded-content"
                                        hx-target="this"
                                    >
                                        @if (! empty($formError))
                                            <div class="tw-text-red-500">{!! $formError !!}</div>
                                        @endif
                                        @foreach ((array) $plugin as $prop => $value)
                                            <input type="hidden" name="plugin[{{ $prop }}]" value="{{ is_array($value) || is_object($value) ? json_encode($value) : $value }}" />
                                        @endforeach
                                        <input class="!tw-mb-none !tw-p-[4px]" type="text" name="plugin[license]" placeholder="License Key" />
                                        <x-global::button
                                            :tag="'button'"
                                            :type="'secondary'"
                                        >Install</x-global::button>
                                        <div class="htmx-indicator">
                                            <x-global::loadingText type="text" :count="5" />
                                        </div>
                                    </form>
                                @endif
                            @else
                                <span>This plugin currently isn't available for installation.</span>
                            @endif
                        @endfragment
                    </div>
                </x-global::tabs.content>
            @endforeach
        </x-slot:contents>
    </x-global::tabs>
@endsection
