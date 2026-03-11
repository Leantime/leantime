@dispatchEvent('beforeSelectable')


<div {{ $attributes->merge([ 'class' => 'selectable selectable-'.$name.' tw:center '.($selected == "true" ? 'active' : ''). '' ]) }} id="selectableWrapper-{{ $id }}">

        <div class="selectableContent">
            {{ $slot }}
        </div>

        <input type="{{ $type ?? 'radio' }}"  name="{{ $name }}" {!!  $selected == "true" ? "checked='checked'" : ""  !!} id="selectable-{{ $id }}" value="{{ $value }}" class="selectableRadio tw:hidden"/>
        <label for="selectable-{{ $id }}" class="selectable-label" >
            {{ $label }}
        </label>

</div>

@pushonce('scripts')
    <script>



        function setSelectables() {
            jQuery(".selectable").each(function(){

                jQuery(this).mousedown(function(){
                    jQuery(this).addClass("pushed");
                });
                jQuery(this).mouseup(function(){
                    jQuery(this).removeClass("pushed");
                });

                jQuery(this).click(function(){
                    var name = jQuery(this).find("input").attr("name");
                    var type = jQuery(this).find("input").attr("type");

                    if(type == 'radio') {
                        jQuery(".selectable-" + name).find("input.selectableRadio").removeProp("checked");
                        jQuery(".selectable-" + name).removeClass("active");
                        jQuery(this).addClass("active");
                        jQuery(this).find("input.selectableRadio").prop("checked", true);
                    }

                    if(type=='checkbox') {
                        if( jQuery(this).hasClass("active")) {
                            jQuery(this).removeClass("active");
                            jQuery(this).find("input.selectableRadio").prop("checked", false);
                        }else{
                            jQuery(this).addClass("active");
                            jQuery(this).find("input.selectableRadio").prop("checked", true);
                        }
                    }
                });
            });
        }

        jQuery(document).ready(function() {
            setSelectables();
        });

        if (typeof htmx !== 'undefined') {
            htmx.onLoad(function(){
                setSelectables();
            });
        }


    </script>
@endpushonce


@dispatchEvent('afterSelectableClose')
