@props([
    'plugin'
])

<div style="background: var(--kanban-card-bg); border-radius: var(--box-radius); box-shadow: var(--regular-shadow); padding: 20px; margin-bottom: 12px;">
    <div style="display: flex; align-items: flex-start; gap: 20px;">

        {{-- Plugin image --}}
        <img
            src="{{ $plugin->getPluginImageData() }}"
            width="75"
            height="75"
            style="border-radius: var(--box-radius-small); flex-shrink: 0; object-fit: cover;"
            alt="{{ $plugin->name }}"
        />

        {{-- Content --}}
        <div style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                <strong style="font-size: var(--font-size-l);">{!! $plugin->name !!}</strong>
                @if (!empty($plugin->version))
                    <span style="color: var(--secondary-font-color); font-size: var(--font-size-s);">v{{ $plugin->version }}</span>
                @endif
                @if($plugin instanceof \Leantime\Domain\Plugins\Models\MarketplacePlugin)
                    <span
                        style="font-size: var(--font-size-xs); color: var(--accent1); font-weight: 600;"
                        data-tippy-content="{{ __('marketplace.certified_tooltip') }}"
                    >
                        <x-global::elements.icon name="verified" />
                        Certified
                    </span>
                @endif
            </div>

            <div style="margin-top: 4px; font-size: var(--font-size-s); color: var(--secondary-font-color);">
                <x-globals::inlineLinks :links="$plugin->getMetadataLinks()" />
            </div>

            @if (! empty($desc = $plugin->getCardDesc()))
                <p style="margin: 8px 0 0; font-size: var(--font-size-s); color: var(--primary-font-color); line-height: 1.5;">
                    {!! $desc !!}
                </p>
            @endif

            @if (! empty($price = $plugin->getPrice()))
                <div style="margin-top: 8px; font-size: var(--font-size-s); font-weight: 600; color: var(--primary-font-color);">
                    {!! $price !!}
                </div>
            @endif
        </div>

        {{-- Controls --}}
        <div style="flex-shrink: 0; display: flex; align-items: center;">
            @include($plugin->getControlsView(), ["plugin" => $plugin])
        </div>

    </div>
</div>
