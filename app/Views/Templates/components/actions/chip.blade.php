@props([
    'contentRole' => '',
    'variant' => 'select',           // select (dropdown) | input (display-only)
    'color' => null,                 // hex color — auto-calculates contrast text color
    'selectedClass' => '',
    'selectedKey' => '',
    'parentId' => '',
    'options' => [],
    'extraClass' => '',
    'linkStyle' => '',
    'submit' => "false",
    'colorized' => false,
    'noBg' => false,
    'align' => 'end',
    'headerLabel' => '',
    'leadingVisual' => null,
    'trailingVisual' => null,
    'scale' => null,                 // xs|s|m|l
    'hxPost' => '',
    'hxSwap' => 'none',
    'hxIndicator' => '',
])

@php
    /**
     * Calculate a contrasting text color (black or white) for a given background.
     * Uses WCAG relative luminance formula.
     *
     * @param  string $hexColor  Hex color (e.g., '#FF5733' or 'FF5733')
     * @return string            '#000' for light backgrounds, '#fff' for dark backgrounds
     */
    $contrastColor = function (string $hexColor): string {
        $hex = ltrim($hexColor, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return '#fff'; // fallback for invalid/non-hex (e.g. CSS vars)
        }
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // sRGB linearization
        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        return $luminance > 0.179 ? '#000' : '#fff';
    };

    // Build trigger inline style from color prop or legacy linkStyle
    $triggerStyle = $linkStyle;
    if ($color && !str_starts_with((string)$color, 'var(')) {
        $textColor = $contrastColor($color);
        $triggerStyle = "background-color:{$color}; color:{$textColor};";
    } elseif ($color) {
        // CSS variable — can't compute contrast; let CSS handle text color
        $triggerStyle = "background-color:{$color};";
    }

    // Scale class
    $scaleClass = match($scale) {
        'xs' => 'chip-xs',
        's'  => 'chip-sm',
        'l'  => 'chip-lg',
        default => '',
    };
@endphp

@if($variant === 'input')
    {{-- Display-only chip — no dropdown --}}
    <span {{ $attributes->merge(['class' => 'chip-display ' . $contentRole . ' ' . $selectedClass . ' ' . $scaleClass]) }}
          @if($triggerStyle) style="{{ $triggerStyle }}" @endif
    >
        @if($leadingVisual)
            <x-global::elements.icon :name="$leadingVisual" size="xs" />
        @endif
        <span class="text">{{ $slot }}</span>
        @if($trailingVisual)
            <x-global::elements.icon :name="$trailingVisual" size="xs" />
        @endif
    </span>
@else
    {{-- Dropdown chip (select variant — default) --}}
    <div {{ $attributes->merge(['class' => '']) }}>
        <div class="dropdown ticketDropdown {{ $contentRole }}Dropdown {{ $colorized ? 'colorized' : '' }} {{ $noBg ? 'noBg' : '' }} show {{ $extraClass }} {{ $scaleClass }}">
            <a href="javascript:void(0)" style="{{ $triggerStyle }}" class="dropdown-toggle f-left {{ $contentRole }} {{ $selectedClass }}" id="{{ $contentRole }}DropdownMenuLink{{ $parentId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                @if($leadingVisual)
                    <x-global::elements.icon :name="$leadingVisual" size="xs" />
                @endif
                <span class="text">
                    @if(isset($options[$selectedKey]))
                        @if(is_array($options[$selectedKey]))
                            {{ $options[$selectedKey]['name'] }}
                        @else
                            {{ $options[$selectedKey] }}
                        @endif
                    @else
                        {{ __("label.".$contentRole."_unknown") }}
                    @endif
                </span>
                @if($trailingVisual)
                    <x-global::elements.icon :name="$trailingVisual" size="xs" />
                @else
                    &nbsp;<x-global::elements.icon name="arrow_drop_down" size="xs" />
                @endif
            </a>
            <ul class="dropdown-menu" aria-labelledby="{{ $contentRole }}DropdownMenuLink{{ $parentId }}">
                <li class="nav-header border">{{ $headerLabel ?: __("label.select_".$contentRole) }}</li>
                @foreach ($options as $key => $value)
                    @php
                        $itemClass = is_array($value) ? ($value['class'] ?? '') : '';
                        $isColor = str_starts_with((string)$itemClass, '#');
                        $itemLabel = is_array($value) ? $value['name'] : $value;

                        // Auto-contrast for hex-colored dropdown items
                        $itemStyle = '';
                        if ($isColor) {
                            $itemTextColor = $contrastColor($itemClass);
                            $itemStyle = "background-color: {$itemClass}; color: {$itemTextColor};";
                        }
                    @endphp
                    <li class='dropdown-item'>
                        <a href='javascript:void(0);'
                           class='dropdownPillLink {{ $contentRole }}-bg-{{ $key }} {{ $isColor ? "" : $itemClass }}'
                           @if($itemStyle) style="{{ $itemStyle }}" @endif
                           id='{{ $contentRole }}Change{{ $parentId }}{{ $key }}'
                           onclick="jQuery('#dropdownPill-{{ $parentId }}-{{ $contentRole }}').val('{{ $key }}'); @if($submit !== "false") document.querySelector('{{ $submit }}').submit(); @endif document.activeElement.blur();"
                           data-label='{{ $itemLabel }}'
                           data-value='{{ $parentId }}_{{ $key }}{{ $itemClass ? "_" . $itemClass : "" }}'
                           @if($hxPost)
                               hx-post="{{ $hxPost }}"
                               hx-swap="{{ $hxSwap }}"
                               hx-vals='{"id": "{{ $parentId }}", "{{ $contentRole }}": "{{ $key }}"}'
                               @if($hxIndicator) hx-indicator="{{ $hxIndicator }}" @endif
                           @endif
                        >
                            {{ $itemLabel }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <input type="hidden" name="{{ $contentRole }}" value="{{ $selectedKey }}" id="dropdownPill-{{ $parentId }}-{{ $contentRole }}" />
    </div>
@endif
