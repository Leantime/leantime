@props([
    'contentRole' => '',          // ''(none) | default | primary | secondary | tertiary(=ghost) | accent | link
    'state' => '',                // info | warning | danger | success
    'scale' => '',                // xs | s | m | l | xl
    'variant' => '',              // 'outline' = outline style (btn-outline / btn-{state}-outline)
    'tag' => 'button',            // a | button | input  (the polymorphic element)
    'link' => null,               // href, when tag="a"; null => emit no href (e.g. <a onclick> with no href)
    'inputType' => null,          // submit | button | reset, when tag="button"/"input" (default below)
    'leadingVisual' => '',        // icon class, e.g. "fa fa-plus"
    'trailingVisual' => '',       // icon class
    'labelText' => '',            // text label; falls back to the slot
])

{{--
    forms.button — NO-OP button.

    Renders the exact Bootstrap/forms.css classes the app uses TODAY (btn, btn-primary,
    btn-danger, btn-small, …) so there is zero visual change. Call-sites are written against
    the canonical prop vocabulary (contentRole/state/scale); at design time ONLY the maps
    below + the CSS change, restyling every button from one place. See COMPONENTS.md.

    Migration cheatsheet (today's class -> prop):
      btn-primary -> contentRole="primary"      btn-default     -> contentRole="default"
      btn-secondary -> contentRole="secondary"  btn-transparent -> contentRole="ghost"
      btn-link    -> contentRole="link"         btn-danger/info/success/warning -> state="…"
      btn-small/btn-large -> scale="s"/"l"      extra classes -> pass as class="…"
    JS-coupled buttons (.dropdown-toggle) are migrated in the dropdown phase, not here.
--}}
@php
    // Canonical role -> the class the app renders today (no-op mapping).
    $roleClass = match ($contentRole) {
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'default' => 'btn-default',
        'tertiary', 'ghost' => 'btn-transparent',
        'accent' => 'btn-primary',
        'link' => 'btn-link',
        default => '',
    };

    // State color is mutually exclusive with the role color today (a danger button is
    // `btn btn-danger`, not `btn-primary btn-danger`). If a state is given, it wins.
    $stateClass = match ($state) {
        'danger' => 'btn-danger',
        'warning' => 'btn-warning',
        'success' => 'btn-success',
        'info' => 'btn-info',
        default => '',
    };

    $scaleClass = match ($scale) {
        'xs', 's', 'sm' => 'btn-small',
        'l', 'lg', 'xl' => 'btn-large',
        default => '',
    };

    // variant="outline" selects the outline button style — btn-outline, or btn-{state}-outline
    // (e.g. btn-danger-outline). This is the same style the edit-ticket save / "Save & Close"
    // buttons use. Outline overrides the role color.
    if ($variant === 'outline') {
        $colorClass = $state !== '' ? 'btn-'.$state.'-outline' : 'btn-outline';
    } else {
        $colorClass = $stateClass !== '' ? $stateClass : $roleClass;
    }
    $classes = trim('btn '.$colorClass.' '.$scaleClass);

    // Inner content: leading icon + (labelText or slot) + trailing icon, matching the
    // hand-written "<i class="fa …"></i> Label" markup buttons use today.
    $hasLabel = trim($labelText) !== '';
@endphp

@if ($tag === 'input')
    <input
        type="{{ $inputType ?? 'submit' }}"
        value="{{ $hasLabel ? $labelText : trim($slot) }}"
        {{ $attributes->merge(['class' => $classes]) }}
    />
@elseif ($tag === 'a')
    {{-- emit href only when a link is given, so <a onclick> without href stays href-less --}}
    <a {{ $attributes->merge(['class' => $classes] + ($link !== null ? ['href' => $link] : [])) }}>
        @if ($leadingVisual)<i class="{{ $leadingVisual }}"></i> @endif{{ $hasLabel ? $labelText : $slot }}@if ($trailingVisual) <i class="{{ $trailingVisual }}"></i>@endif
    </a>
@else
    {{-- Bare <button> emits NO type so the native default (submit inside a form) is preserved;
         pass inputType only when the source had an explicit type. --}}
    <button @if ($inputType !== null) type="{{ $inputType }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($leadingVisual)<i class="{{ $leadingVisual }}"></i> @endif{{ $hasLabel ? $labelText : $slot }}@if ($trailingVisual) <i class="{{ $trailingVisual }}"></i>@endif
    </button>
@endif
