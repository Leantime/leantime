@props([
    'headings' => '',
    'contents',
    'formHash' => md5(CURRENT_URL."tabs".$headings),
    'variant' => 'boxed',
    'size' => ''
])

@php
    $variantClass = $variant ? 'tabs-'.$variant : '';
    $sizeClass = $size && $size !== 'md' ? 'tabs-'.$size : '';
@endphp

<div {{ $attributes->merge(['class' => 'tabsWrapper tabs-component-'.$formHash ]) }}>
    <div role="tablist" {{ $headings->attributes->merge(['class' => 'tabs-headings tabs '.$variantClass.' '.$sizeClass ]) }}>
        {{ $headings }}
    </div>

    <div {{ $contents->attributes->merge(['class' => 'tabs-content' ]) }}>
        {{ $contents }}
    </div>
</div>


<script type="text/javascript">
    htmx.onLoad(function () {

        let currentTab = localStorage.getItem("tabSelection-{{ $formHash }}");

        //let activeTabIndex;

        if (typeof currentTab !== 'undefined') {

            jQuery('.tabs-component-{{ $formHash }} .tabs-headings a').removeClass("tab-active");
            jQuery('.tabs-component-{{ $formHash }} .tabs-content div').removeClass("show");

            jQuery('.tabs-component-{{ $formHash }} .tabs-headings a[data-tab-id="'+currentTab+'"]').addClass("tab-active");
            jQuery('.tabs-component-{{ $formHash }} .tabs-content div'+currentTab).addClass("show");

        }

        jQuery('.tabs-component-{{ $formHash }} .tabs-headings a').each(function() {

            jQuery(this).on("click", function( event ) {
                event.stopPropagation();

                let tabId = jQuery(this).data("tab-id");
                jQuery('.tabs-component-{{ $formHash }} .tabs-headings a').removeClass("tab-active");

                jQuery(this).addClass("tab-active");
                jQuery('.tabs-component-{{ $formHash }} .tabs-content div').removeClass("show");

                jQuery('.tabs-component-{{ $formHash }} .tabs-content div'+tabId).addClass("show");

                localStorage.setItem("tabSelection-{{ $formHash }}", tabId);

            });


        });

    });
</script>

