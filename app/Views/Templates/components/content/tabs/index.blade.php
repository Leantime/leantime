@props([
    'headings',
    'contents',
    'formHash' => md5(CURRENT_URL."tabs".mt_rand(0,100)),
])

<div {{ $attributes->merge(['class' => 'tabsComponent tabsComponent-'.$formHash.' tabbedwidget tab-primary']) }}>
    <ul {{ $headings->attributes }}>
        {{ $headings }}
    </ul>

    {{ $contents }}
</div>


<script type="text/javascript">
    htmx.onLoad(function () {
        jQuery('.tabsComponent-{{ $formHash }}').tabs();
    });

</script>

