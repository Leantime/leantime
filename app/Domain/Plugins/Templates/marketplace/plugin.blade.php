<div class="tw-w-1/3 tw-p-4 tw-border tw-bg-[var(--secondary-background)] tw-shadow-[var(--large-shadow)] tw-rounded-[var(--box-radius)]">
    @if (! empty($plugin['featured_image']))
        <img src="{{ $plugin['featured_image'] }}" alt="{{ $plugin['post_title'] }}" class="tw-w-full tw-h-auto tw-mb-4">
    @endif

    @if (! empty($plugin['post_title']))
        <h2>{{ $plugin['post_title'] }}</h2>
    @endif

    @if (! empty($plugin['excerpt']))
        <p>{{ $plugin['excerpt'] }}</p>
    @endif

    <a
        class="btn btn-primary"
        href="#/plugins/details/{!! $plugin['identifier'] !!}"
    >Learn More</a>
</div>
