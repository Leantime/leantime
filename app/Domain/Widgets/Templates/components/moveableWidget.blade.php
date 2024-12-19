<div class="grid-stack-item" {{ $attributes }}>
    <div class="grid-stack-item-content {{ ($background == "default") ? "maincontentinner" : $background  }} p-none">
        <div class="{{ ($background == "default") ? "pb-l" : "" }}">
            <div class="stickyHeader w-full relative" >
                <div class="grid-handler-top h-[40px] cursor-grab float-left" style="margin: 0 5px;" >
                    <i class="fa-solid fa-grip-vertical"></i>
                </div>
                @if($name != '' && $noTitle == false)
                    <h5 class="subtitle pb-m float-left mr-sm">{{ __($name) }}</h5>
                @endif

                <x-global::actions.dropdown content-role="tertiary" position="bottom" align="end"
                    class="float-right" button-shape="circle"
                >
                    <x-slot:label-text>
                        <i class='fa fa-ellipsis-v'></i>
                    </x-slot:label-text>
                    <x-slot:menu>
                        <!-- Resize content -->
                        <x-global::actions.dropdown.item variant="link"
                            class="fitContent"
                        >
                            <i class="fa-solid fa-up-right-and-down-left-from-center"></i> Resize to fit content
                        </x-global::actions.dropdown.item>

                        <!-- Hide Widget -->
                        @if(empty($alwaysVisible))
                            <x-global::actions.dropdown.item variant="link"
                                class="removeWidget"
                            >
                                <i class="fa fa-eye-slash"></i> Hide
                            </x-global::actions.dropdown.item>
                        @endif
                    </x-slot:menu>
                </x-global::actions.dropdown>

            </div>
            <span class="clearall"></span>
            <div class="widgetContent {{ ($background == "default") ? 'px-m' : '' }}">
                {{ $slot }}
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {

        jQuery('.fitContent').on('click', function(e) {
            const gridItem = jQuery(this).closest('.grid-stack-item')[0];
            leantime.widgetController.resizeWidget(gridItem);
        })

        jQuery('#removeWidget').on('click', function(e) {
            // const gridItem = jQuery(this).closest('.grid-stack-item')[0];
            leantime.widgetController.toggleWidgetVisibility();
        });

    });
</script>
