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
        `
                        @if (! empty($plugin->marketplaceId))
                            <form class="tw-flex tw-gap-2 tw-items-center" hx-encoding="multipart/form-data" hx-post="{{ BASE_URL }}/hx/plugins/marketplaceplugins/installplugin">
                                <input type="hidden" name="plugin" value="{{ $plugin->marketplaceId }}" />
                                <input class="!tw-mb-none !tw-p-[4px]" type="text" name="licenseKey" placeholder="License Key" />
                                <x-global::button
                                    :tag="'button'"
                                    :type="'secondary'"
                                    hx-trigger="click"
                                >Install</x-global::button>
                            </form>
                        @else
                            <span>This plugin currently isn't available for installation.</span>
                        @endif
                    </div>
                </x-global::tabs.content>
            @endforeach
        </x-slot:contents>
    </x-global::tabs>
@endsection
