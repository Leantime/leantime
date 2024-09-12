@props([
    'type' => '',
    'selectedClass' => '',
    'selectedKey' => '',
    'parentId' => '',
    'options' => [],
    'extraClass' => '',
    'linkStyle' => '',
    'submit' => "false",
    'labelText'=> '',
])

<div {{ $attributes->merge([ 'class' => '' ]) }} >
    <div class="dropdown ticketDropdown {{ $type }}Dropdown show {{ $extraClass }}" id="dropdownPillWrapper-{{ $parentId }}-{{ $type }}">
        <a style="{{ $linkStyle }}" class="dropdown-toggle inline-block {{ $type }} {{ $selectedClass }} {{ $type }}-bg-{{ $selectedClass }}" href="javascript:void(0);" role="button" id="{{ $type }}DropdownMenuLink{{ $parentId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="text">
                @if(isset($options[$selectedKey]))
                    @if(is_array($options[$selectedKey]))
                        {{ $options[$selectedKey]['name'] }}
                    @else
                        {{ $options[$selectedKey] }}
                    @endif
                @elseif(!empty($labelText))
                    {!! $labelText !!}
                @else
                    {{ __("label.".$type."_unknown") }}
                @endif
            </span>
            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
        </a>
        <ul class="dropdown-menu" aria-labelledby="{{ $type  }}DropdownMenuLink{{ $parentId }}">
            {{ $slot }}
        </ul>
    </div>
    <input type="hidden" name="{{ $type }}Field" value="{{ $selectedKey }}" id="dropdownPillInput-{{ $parentId }}-{{ $type }}" />
</div>

<script>

    jQuery.fn.removeClassStartingWith = function (filter) {
        jQuery(this).removeClass(function (index, className) {
            return (className.match(new RegExp("\\S*" + filter + "\\S*", 'g')) || []).join(' ')
        });
        return this;
    };

    function pillSelect(key, bgClass, type, parentId) {

        jQuery('#dropdownPillInput-' + parentId + '-'+ type).val(key);

        let selectedLabel = jQuery('#dropdownPillWrapper-' + parentId + '-'+ type + ' #'+ type +'Change'+parentId+''+key).attr("data-label");
        let selectedValue = jQuery('#dropdownPillWrapper-' + parentId + '-'+ type + ' #'+ type +'Change'+parentId+''+key).attr("data-label");
        jQuery('#dropdownPillWrapper-' + parentId + '-'+ type + ' .dropdown-toggle .text').text(selectedLabel);
        jQuery('#dropdownPillWrapper-' + parentId + '-'+ type + ' .dropdown-toggle').removeClassStartingWith(type+'-bg-');
        jQuery('#dropdownPillWrapper-' + parentId + '-'+ type + ' .dropdown-toggle').addClass(type+'-bg-'+bgClass);


    }

</script>
