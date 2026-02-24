@props([
    'headings',
    'contents',
])

<div {{ $attributes->merge(['class' => 'tabsComponent tabbedwidget tab-primary']) }}>
    <ul {{ $headings->attributes->merge(['class' => 'nav nav-tabs', 'role' => 'tablist']) }}>
        {{ $headings }}
    </ul>

    {{ $contents }}
</div>

@once('scripts')
    <script type="text/javascript">
        jQuery('.tabsComponent').tabs();
    </script>
@endonce
