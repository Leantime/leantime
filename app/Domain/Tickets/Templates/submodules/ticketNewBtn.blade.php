@php
    $newField = $tpl->get('newField');
@endphp

@if($login::userIsAtLeast($roles::$editor) && !empty($newField))
    <div class="btn-group tw:float-left" style="margin-right:5px;">
        <button class="btn btn-primary tw:btn tw:btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
            {!! __('links.new_with_icon') !!} <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            @foreach($newField as $option)
                <li>
                    <a href="{{ $option['url'] ?? '' }}"
                       class="{{ $option['class'] ?? '' }}">
                        {{ !empty($option['text']) ? __($option['text']) : '' }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
