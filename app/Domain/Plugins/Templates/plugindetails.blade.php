@extends($layout)

@section('content')
    <div class="tw-max-h-[90vh] tw-w-[40vw] tw-flex tw-flex-col tw-gap-base">
        <div class="tw-flex tw-gap-base tw-items-center">
            <img src="{{ $plugin->icon }}" width="175" height="175" class="tw-rounded">
            <div class="tw-flex tw-flex-col tw-gap-base">
                <h2 class="tw-text-2xl tw-flex tw-flex-col tw-gap-base">
                    <span>{!! $plugin->name !!}</span>
                    @if (!empty($plugin->vendorDisplayName) && !empty($plugin->vendorId))
                        <small>{{ __('text.by') }} <a href="/plugins/marketplace?vendor_id={{ $plugin->vendorId }}">{{ $plugin->vendorDisplayName }}</a></small>
                    @endif
                </h2>
                <p>
                    @if ((int) $plugin->reviewCount > 0)
                        <strong>{{ __('label.reviews_colon') }}</strong> {{ $plugin->reviewCount }}<br>
                    @endif

                    @if ((int) $plugin->rating > 0)
                        <strong>{{ __('label.rating_colon') }}</strong> {{ $plugin->rating }}<br>
                    @endif

                    @if (! empty($plugin->categories))
                        <strong>{{ __('label.categories_colon') }}</strong> @foreach ($plugin->categories as $category)
                            <x-global::elements.badge>{{ $category['name'] }}</x-global::elements.badge>
                        @endforeach<br>
                    @endif

                    @if (! empty($plugin->tags))
                        <strong>{{ __('label.tags_colon') }}</strong> @foreach ($plugin->tags as $tag)
                            <x-global::elements.badge tone="accent" :href="'/plugins/marketplace?tag=' . $tag['slug']">{{ $tag['name'] }}</x-global::elements.badge>
                        @endforeach<br>
                    @endif
                </p>
            </div>
        </div>

        @php
            // First visible tab is the initially-selected one (each section is conditional).
            $pluginTabOrder = array_values(array_filter([
                ! empty($plugin->description) ? 'overview' : null,
                $plugin->reviewCount > 0 ? 'reviews' : null,
                ! empty($plugin->compatibility) ? 'compatibility' : null,
            ]));
            $pluginFirstTab = $pluginTabOrder[0] ?? null;
        @endphp
        @if ($pluginFirstTab !== null)
        <x-global::navigation.tabs group="plugindetails" variant="underline" :label="__('label.plugin_details_sections')">
            @if (! empty($plugin->description))
                <x-global::navigation.tabs.tab name="overview" :selected="$pluginFirstTab === 'overview'">{{ __('label.overview') }}</x-global::navigation.tabs.tab>
            @endif

            @if ($plugin->reviewCount > 0)
                <x-global::navigation.tabs.tab name="reviews" :count="$plugin->reviewCount" :selected="$pluginFirstTab === 'reviews'">{{ __('label.reviews') }}</x-global::navigation.tabs.tab>
            @endif

            @if (! empty($plugin->compatibility))
                <x-global::navigation.tabs.tab name="compatibility" :selected="$pluginFirstTab === 'compatibility'">{{ __('label.compatibility') }}</x-global::navigation.tabs.tab>
            @endif
        </x-global::navigation.tabs>
        @endif

        <div class="tw-overflow-y-scroll tw-max-h-[600px]">
                @if (! empty($plugin->description))
                    <x-global::navigation.tabs.panel name="overview" group="plugindetails">
                        <div class="tw-pr-xs mce-content-body">{!! $plugin->description !!}</div>
                    </x-global::navigation.tabs.panel>
                @endif

                @if ($plugin->reviewCount > 0)
                    <x-global::navigation.tabs.panel name="reviews" group="plugindetails">
                        <div class="tw-flex tw-flex-col tw-gap-base">
                            @foreach($plugin->reviews as $review)
                                @if (is_array($review) || is_object($review))
                                    <div class="tw-border-b tw-border-gray-200 tw-pb-sm tw-mb-sm">
                                        @if (! empty($review['author'] ?? null))
                                            <strong>{{ $review['author'] }}</strong>
                                        @endif
                                        @if (! empty($review['rating'] ?? null))
                                            <span class="tw-text-sm tw-text-gray-500">({{ $review['rating'] }}/5)</span>
                                        @endif
                                        <p>{{ $review['content'] ?? $review['review'] ?? $review['text'] ?? '' }}</p>
                                    </div>
                                @else
                                    <p>{{ $review }}</p>
                                @endif
                            @endforeach
                        </div>
                    </x-global::navigation.tabs.panel>
                @endif

                @if (! empty($plugin->compatibility))
                    <x-global::navigation.tabs.panel name="compatibility" group="plugindetails">
                        <table class="tw-w-full tw-text-left tw-pt-base">
                            <thead>
                                <tr>
                                    <th>{{ __('label.plugin_version_colon') }}</th>
                                    <th>{{ __('label.compatible_versions_colon') }}</th>
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
                    </x-global::navigation.tabs.panel>
                @endif
        </div>

        <div class="tw-flex tw-justify-between tw-items-center">
            @if (! empty($plugin->marketplaceUrl))
                <x-global::button
                    :link="$plugin->marketplaceUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                >{{ __('links.get_a_license') }}</x-global::button>
            @else
                <span>{{ __('text.plugin_not_for_sale') }}</span>
            @endif

            @fragment('plugin-installation')
                @if (! empty($plugin->marketplaceId))
                    @if (isset($formNotification) && ! empty($formNotification))
                        <div class="tw-text-green-500">{!! $formNotification !!}</div>
                    @else
                        <div id="installForm{{ $plugin->marketplaceId }}">


                            @if (! empty($formError))
                                <div class="tw-text-red-500">{!! $formError !!}</div>
                            @endif

                            @if($isBundle === false)
                                <form
                                class="tw-flex tw-gap-2 tw-items-center"
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
                                <x-global::forms.select width="auto" class="!tw-mb-none !tw-py-[4px]" name="plugin[version]">
                                    @foreach ($plugin->compatibility as $compatibility)
                                        <option value="{{ $compatibility['version_number'] }}">{{ $compatibility['version_number'] }}</option>
                                    @endforeach
                                </x-global::forms.select>
                                <x-global::forms.text-input class="!tw-mb-none !tw-py-[4px]" name="plugin[license]" :placeholder="__('label.license_key')" />
                                <x-global::button
                                    :tag="'button'"
                                    :type="'secondary'"
                                >{{ __('buttons.install') }}</x-global::button>
                                <div class="htmx-indicator-small">
                                    <x-global::loader id="loadingthis" size="25px" />
                                </div>
                            </form>
                            @endif


                        </div>
                    @endif
                @else
                    <span>{{ __('text.plugin_not_installable') }}</span>
                @endif
            @endfragment
        </div>
    </div>
@endsection
