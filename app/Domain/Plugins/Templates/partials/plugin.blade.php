@props([
    'plugin'
])

<div class="tw:bg-[var(--kanban-card-bg)] tw:rounded-[var(--box-radius)] tw:shadow-[var(--regular-shadow)] tw:p-5 tw:mb-3">
    <div class="tw:flex tw:items-start tw:gap-5">

        {{-- Plugin image --}}
        <img
            src="{{ $plugin->getPluginImageData() }}"
            width="75"
            height="75"
            class="tw:rounded-[var(--box-radius-small)] tw:shrink-0 tw:object-cover"
            alt="{{ $plugin->name }}"
        />

        {{-- Content --}}
        <div class="tw:flex-1 tw:min-w-0">
            <div class="tw:flex tw:items-baseline tw:gap-2 tw:flex-wrap">
                <strong>{!! $plugin->name !!}</strong>
                @if (!empty($plugin->version))
                    <span class="tw:text-[color:var(--secondary-font-color)]">v{{ $plugin->version }}</span>
                @endif
                @if($plugin instanceof \Leantime\Domain\Plugins\Models\MarketplacePlugin)
                    <span
                        class="tw:font-semibold tw:text-[color:var(--accent1)]"
                        data-tippy-content="{{ __('marketplace.certified_tooltip') }}"
                    >
                        <x-globals::elements.icon name="verified" />
                        Certified
                    </span>
                @endif
            </div>

            <div class="tw:mt-1 tw:text-[color:var(--secondary-font-color)]">
                <x-globals::inlineLinks :links="$plugin->getMetadataLinks()" />
            </div>

            @if (! empty($desc = $plugin->getCardDesc()))
                <p class="tw:mt-2 tw:text-[color:var(--primary-font-color)] tw:leading-normal">
                    {!! $desc !!}
                </p>
            @endif

            @if (! empty($price = $plugin->getPrice()))
                <div class="tw:mt-2 tw:font-semibold tw:text-[color:var(--primary-font-color)]">
                    {!! $price !!}
                </div>
            @endif
        </div>

        {{-- Controls --}}
        <div class="tw:shrink-0 tw:flex tw:items-center">
            @include($plugin->getControlsView(), ["plugin" => $plugin])
        </div>

    </div>
</div>
