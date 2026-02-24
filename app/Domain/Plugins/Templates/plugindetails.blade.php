@extends($layout)

@section('content')
    <div class="tw:max-h-[90vh] tw:w-[40vw] tw:flex tw:flex-col tw:gap-base">
        <div class="tw:flex tw:gap-base tw:items-center">
            <img src="{{ $plugin->icon }}" width="175" height="175" class="tw:rounded">
            <div class="tw:flex tw:flex-col tw:gap-base">
                <h2 class="tw:text-2xl tw:flex tw:flex-col tw:gap-base">
                    <span>{!! $plugin->name !!}</span>
                    @if (!empty($plugin->vendorDisplayName) && !empty($plugin->vendorId))
                        <small>{{ __('text.by') }} <a href="/plugins/marketplace?vendor_id={{ $plugin->vendorId }}">{{ $plugin->vendorDisplayName }}</a></small>
                    @endif
                </h2>
                <p>
                    @if ((int) $plugin->reviewCount > 0)
                        <strong>Reviews:</strong> {{ $plugin->reviewCount }}<br>
                    @endif

                    @if ((int) $plugin->rating > 0)
                        <strong>Rating:</strong> {{ $plugin->rating }}<br>
                    @endif

                    @if (! empty($plugin->categories))
                        <strong>Categories:</strong> @foreach ($plugin->categories as $category)
                            <x-globals::elements.badge :asLink="false">{{ $category['name'] }}</x-globals::elements.badge>
                        @endforeach<br>
                    @endif

                    @if (! empty($plugin->tags))
                        <strong>Tags:</strong> @foreach ($plugin->tags as $tag)
                            <x-globals::elements.badge :asLink="true" :url="'/plugins/marketplace?tag=' . $tag['slug']">{{ $tag['name'] }}</x-globals::elements.badge>
                        @endforeach<br>
                    @endif
                </p>
            </div>
        </div>

        <x-globals::navigations.tabs class="tw:overflow-y-scroll tw:max-h-[600px] tw:border-b !tw:border-b-gray-500">
            <x-slot:headings class="tw:sticky tw:top-0 !tw:bg-[--secondary-background]">
                @if (! empty($plugin->description))
                    <x-globals::navigations.tabs.heading name="overview">Overview</x-globals::navigations.tabs.heading>
                @endif

                @if ($plugin->reviewCount > 0)
                    <x-globals::navigations.tabs.heading name="reviews">Reviews</x-globals::navigations.tabs.heading>
                @endif

                @if (! empty($plugin->compatibility))
                    <x-globals::navigations.tabs.heading name="compatibility">Compatibility</x-globals::navigations.tabs.heading>
                @endif
            </x-slot:headings>

            <x-slot:contents>
                @if (! empty($plugin->description))
                    <x-globals::navigations.tabs.content name="overview">
                        <div class="tw:pr-xs mce-content-body">{!! $plugin->description !!}</div>
                    </x-globals::navigations.tabs.content>
                @endif

                @if ($plugin->reviewCount > 0)
                    <x-globals::navigations.tabs.content name="reviews">
                        <div class="tw:flex tw:flex-col tw:gap-base">
                            @foreach($plugin->reviews as $review)
                                @if (is_array($review) || is_object($review))
                                    <div class="tw:border-b tw:border-gray-200 tw:pb-sm tw:mb-sm">
                                        @if (! empty($review['author'] ?? null))
                                            <strong>{{ $review['author'] }}</strong>
                                        @endif
                                        @if (! empty($review['rating'] ?? null))
                                            <span class="tw:text-sm tw:text-gray-500">({{ $review['rating'] }}/5)</span>
                                        @endif
                                        <p>{{ $review['content'] ?? $review['review'] ?? $review['text'] ?? '' }}</p>
                                    </div>
                                @else
                                    <p>{{ $review }}</p>
                                @endif
                            @endforeach
                        </div>
                    </x-globals::navigations.tabs.content>
                @endif

                @if (! empty($plugin->compatibility))
                    <x-globals::navigations.tabs.content name="compatibility">
                        <table class="tw:w-full tw:text-left tw:pt-base">
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
                                        <td>{{ $compatibility['supported_version_from'] }} - {{ $compatibility['supported_version_to'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-globals::navigations.tabs.content>
                @endif
            </x-slot:contents>
        </x-globals::navigations.tabs>

        <div class="tw:flex tw:justify-between tw:items-center">
            @if (! empty($plugin->marketplaceUrl))
                <x-globals::forms.button
                    :link="$plugin->marketplaceUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                >Get a license</x-globals::forms.button>
            @else
                <span>This plugin currently isn't available for purchase.</span>
            @endif

            @fragment('plugin-installation')
                @if (! empty($plugin->marketplaceId))
                    @if (isset($formNotification) && ! empty($formNotification))
                        <div class="tw:text-green-500">{!! $formNotification !!}</div>
                    @else
                        <div id="installForm{{ $plugin->marketplaceId }}">


                            @if (! empty($formError))
                                <div class="tw:text-red-500">{!! $formError !!}</div>
                            @endif

                            @if($isBundle === false)
                                <form
                                class="tw:flex tw:gap-2 tw:items-center"
                                hx-post="{{ BASE_URL }}/hx/plugins/details/install"
                                hx-swap="outerHTML"
                                hx-indicator=".htmx-indicator-small, .htmx-loaded-content"
                                hx-target="#installForm{{ $plugin->marketplaceId }}"
                            >
                                @php
                                    if (isset($plugin->version)) {
                                        unset($plugin->version);
                                    }
                                @endphp
                                @foreach ((array) $plugin as $prop => $value)
                                    <input type="hidden" name="plugin[{{ $prop }}]" value="{{ is_array($value) || is_object($value) ? json_encode($value) : $value }}" />
                                @endforeach
                                <x-globals::forms.select class="!tw:mb-none !tw:p-[4px]" name="plugin[version]">
                                    @foreach ($plugin->compatibility as $compatibility)
                                        <option value="{{ $compatibility['version_number'] }}">{{ $compatibility['version_number'] }}</option>
                                    @endforeach
                                </x-globals::forms.select>
                                <input class="!tw:mb-none !tw:p-[4px]" type="text" name="plugin[license]" placeholder="License Key" />
                                <x-globals::forms.button
                                    :tag="'button'"
                                    :type="'secondary'"
                                >Install</x-globals::forms.button>
                                <div class="htmx-indicator-small">
                                    <x-globals::feedback.loading id="loadingthis" size="25px" />
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
