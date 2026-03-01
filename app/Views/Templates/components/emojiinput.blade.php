
@props([
    'value' => '',
    'id' => '',
    'placeholder' => '',
    'class' => '',
    'name' => ''
])

@php
    $uniqueId = uniqid();
@endphp

<div class="emojiInput">
    <input type="text" name="{{ $name }}" {{ $attributes->merge(['class' => 'emojifield emojiFieldId'.$uniqueId.' '.$class]) }} value="{{ $value }}" placeholder="{{ $placeholder }}" id="{{ $id  }}" />
    <a class="emojibtn emojibtnId{{ $uniqueId }}" href="javascript:void(0);"><x-global::elements.icon name="sentiment_satisfied" /></a>
</div>
<script>
    jQuery(document).ready(function(){
        new EmojiPicker({
            trigger: [
                {
                    selector: '.emojibtnId{{ $uniqueId }}',
                    insertInto: '.emojiFieldId{{ $uniqueId }}'

                }
            ],
            closeButton: true,
            specialButtons: 'green' // #008000, rgba(0, 128, 0);
        });

    });

</script>
<style>
    .emojiInput {
        position:relative;
    }

    .emojiInput a.emojibtn {
        font-size:var(--font-size-xxl);
        color:var(--neutral);
        margin-left: -35px;
        background:var(--secondary-background);
        position:absolute;
        right: 7px;
        top: 8px;
    }

    .fg-emoji-container {
        box-shadow:var(--large-shadow);
    }
    .fg-emoji-nav {
        background-color: var(--secondary-background);
    }
    .fg-emoji-nav li a svg {
        fill: var(--primary-font-color);
    }
    .fg-emoji-picker-search {
        position: relative;
        margin-top: 15px;
        padding: 0px 10px;
    }
    .fg-emoji-list li {
        height:30px;
    }
    .fg-picker-special-buttons {
        display:none;
    }

    .fg-emoji-picker-category-title {
        margin-top:10px;
    }

    .emojifield {
        width:100%;
    }
</style>
