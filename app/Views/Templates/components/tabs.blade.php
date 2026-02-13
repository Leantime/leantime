@props([
    'headings',
    'contents',
])

<div {{ $attributes->merge(['class' => 'tabsComponent tabbedwidget tab-primary']) }}>
    <ul {{ $headings->attributes->merge(['class' => 'tw:tabs tw:tabs-bordered', 'role' => 'tablist']) }}>
        {{ $headings }}
    </ul>

    {{ $contents }}
</div>

@once('scripts')
    <script type="text/javascript">
        jQuery('.tabsComponent').tabs();
    </script>
@endonce
