<?php
$newField        = $tpl->get('newField');
?>
<?php if ($login::userIsAtLeast($roles::$editor) && !empty($newField)) { ?>
    <div class="btn-group pull-left" style="margin-right:5px;">
        <?php
        $labelText = $tpl->__("links.new_with_icon") . ' <span class="caret"></span>';
    ?>
    
    <x-global::actions.dropdown 
        :labelText="html_entity_decode($labelText)"
        class="btn btn-primary dropdown-toggle"
        align="start"
        contentRole="menu"
    >
        <x-slot:menu>
            @foreach ($newField as $option)
                <x-global::actions.dropdown.item 
                    href="{{ !empty($option['url']) ? $option['url'] : 'javascript:void(0);' }}"
                    class="{{ !empty($option['class']) ? $option['class'] : '' }}"
                >
                    {{ !empty($option['text']) ? $tpl->__($option['text']) : '' }}
                </x-global::actions.dropdown.item>
            @endforeach
        </x-slot:menu>
    </x-global::actions.dropdown>
    
    </div>
<?php } ?>
