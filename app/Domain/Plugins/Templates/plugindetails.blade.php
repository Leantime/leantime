@extends($layout)

@section('content')
    <div class="max-h-[80vh] flex flex-col gap-base">
        <div class="flex gap-base items-center">
            <img src="{{ $plugin->icon }}" width="175" height="175" class="rounded">
            <div class="flex flex-col gap-base">
                <h2 class="text-2xl flex flex-col gap-base">
                    <span>{{ $plugin->name }}</span>
                    @if (!empty($plugin->vendorDisplayName) && !empty($plugin->vendorId))
                        <small>{{ __('text.by') }} <a
                                href="/plugins/marketplace?vendor_id={{ $plugin->vendorId }}">{{ $plugin->vendorDisplayName }}</a></small>
                    @endif
                </h2>
                <p>
                    @if ((int) $plugin->reviewCount > 0)
                        <strong>Reviews:</strong> {{ $plugin->reviewCount }}<br>
                    @endif

                    @if ((int) $plugin->rating > 0)
                        <strong>Rating:</strong> {{ $plugin->rating }}<br>
                    @endif

                    @if (!empty($plugin->categories))
                        <strong>Categories:</strong>
                        @foreach ($plugin->categories as $category)
                            <x-global::elements.badge :asLink="false"
                                :url="'/plugins/marketplace?category=' . $category['slug']">{{ $category['name'] }}</x-global::elements.badge>
                        @endforeach
                        <br>
                    @endif

                    @if (!empty($plugin->tags))
                        <strong>Tags:</strong>
                        @foreach ($plugin->tags as $tag)
                            <x-global::elements.badge :asLink="true"
                                :url="'/plugins/marketplace?tag=' . $tag['slug']">{{ $tag['name'] }}</x-global::elements.badge>
                        @endforeach
                        <br>
                    @endif
                </p>
            </div>
        </div>

        <x-global::content.tabs class="overflow-y-scroll max-h-[500px] border-b !border-b-gray-500">
            <x-slot:headings class="sticky top-0 !bg-[--secondary-background]">
                @if (!empty($plugin->description))
                    <x-global::content.tabs.heading name="overview">Overview</x-global::content.tabs.heading>
                @endif

                @if ($plugin->reviewCount > 0)
                    <x-global::content.tabs.heading name="reviews">Reviews</x-global::content.tabs.heading>
                @endif

                @if (!empty($plugin->compatibility))
                    <x-global::content.tabs.heading name="compatibility">Compatibility</x-global::content.tabs.heading>
                @endif
            </x-slot:headings>

            <x-slot:contents>
                @if (!empty($plugin->description))
                    <x-global::content.tabs.content name="overview">
                        <div class="max-w-prose mce-content-body">{!! $plugin->description !!}</div>
                    </x-global::content.tabs.content>
                @endif

                @if ($plugin->reviewCount > 0)
                    <x-global::content.tabs.content name="reviews">
                        <div class="flex flex-col gap-base">
                            @foreach ($plugin['reviews'] as $review)
                                <p>{{ $review }}</p>
                            @endforeach
                        </div>
                    </x-global::content.tabs.content>
                @endif

                @if (!empty($plugin->compatibility))
                    <x-global::content.tabs.content name="compatibility">
                        <table class="w-full text-left pt-base">
                            <thead>
                                <tr>
                                    <th>Plugin Version:</th>
                                    <th>Compatible With Leantime Versions:</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($plugin->compatibility as $compatibility)
                                    <tr>
                                        <td>{{ $compatibility['version_number'] }}</td>
                                        <td>{{ $compatibility['supported_version_from'] }} -
                                            {{ $compatibility['supported_version_to'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-global::content.tabs.content>
                @endif
            </x-slot:contents>
        </x-global::content.tabs>

        <div class="flex justify-between items-center">
            @if (!empty($plugin->marketplaceUrl))
                <x-global::forms.button :link="$plugin->marketplaceUrl" target="_blank" rel="noopener noreferrer">Get a
                    license</x-global::forms.button>
            @else
                <span>This plugin currently isn't available for purchase.</span>
            @endif

            @fragment('plugin-installation')
                @if (!empty($plugin->marketplaceId))
                    @if (isset($formNotification) && !empty($formNotification))
                        <div class="text-green-500">{!! $formNotification !!}</div>
                    @else
                        <div id="installForm{{ $plugin->marketplaceId }}">
                            @if (!empty($formError))
                                <div class="text-red-500">{!! $formError !!}</div>
                            @endif

                            @if($isBundle === false)
                                <form
                                    class="flex gap-2 items-center" hx-post="{{ BASE_URL }}/hx/plugins/details/install"
                                    hx-swap="outerHTML"
                                    hx-indicator=".htmx-indicator-small, .htmx-loaded-content"
                                    hx-target="#installForm{{ $plugin->marketplaceId }}">
                                    @php
                                        if (isset($plugin->version)) {
                                            unset($plugin->version);
                                        }
                                    @endphp
                                    @foreach ((array) $plugin as $prop => $value)
                                        <input type="hidden" name="plugin[{{ $prop }}]"
                                            value="{{ is_array($value) || is_object($value) ? json_encode($value) : $value }}" />
                                    @endforeach
                                    <x-global::forms.select class="!mb-none !p-[4px]" name="plugin[version]">
                                        @foreach ($plugin->compatibility as $compatibility)
                                            <x-global::forms.select.select-option :value="$compatibility['version_number']">
                                                {{ $compatibility['version_number'] }}
                                            </x-global::forms.select.select-option>
                                        @endforeach
                                    </x-global::forms.select>

                                    <input class="!mb-none !p-[4px]" type="text" name="plugin[license]"
                                        placeholder="License Key" />
                                    <x-global::forms.button :tag="'button'" :type="'secondary'">Install</x-global::forms.button>
                                    <div class="htmx-indicator-small">
                                        <x-global::loader id="loadingthis" size="25px" />
                                    </div>
                                </form>
                            @endif


                        </div>
                    @endif
                @else
                    <span>This plugin currently isn't available for installation.</span>
                @endif
            @endfragment
        </div>
    </div>
@endsection
