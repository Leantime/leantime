@props([
    'ticket'     => null,
    'milestones' => [],
    'showLabel'  => false,
])

@php
    $ticketId = $ticket->id ?? '';
    $patchUrl = BASE_URL . '/hx/tickets/ticket/patch/' . $ticketId;
    $hxVals   = json_encode(['id' => (string) $ticketId]);
@endphp

@if($showLabel)
    <label class="control-label">
        <x-global::elements.icon name="label_important" />
        {!! __('label.milestone') !!}
    </label>
@endif

<x-globals::forms.select
    variant="chip"
    name="milestoneid"
    :id="'milestone-chip-' . $ticketId"
    hx-post="{{ $patchUrl }}"
    hx-trigger="change"
    hx-swap="none"
    hx-vals="{{ $hxVals }}"
>
    @php
        $emptyLabel = __('label.no_milestone');
        $emptyHtml  = '<span class="chip-badge state-default"><span class="chip-icon material-symbols-rounded">label_important</span>' . e($emptyLabel) . '</span>';
        $emptySel   = (($ticket->milestoneid ?? '') == '') ? 'selected' : '';
    @endphp
    <option value="" {{ $emptySel }} data-chip-html="{{ $emptyHtml }}">{{ $emptyLabel }}</option>

    @foreach($milestones as $milestone)
        @if(!is_object($milestone))
            @continue
        @endif
        @php
            $color = $milestone->tags ?? '#b0b0b0';
            $style = '';
            if (str_starts_with((string)$color, '#')) {
                $hex = ltrim((string)$color, '#');
                if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
                $fgColor = '#fff';
                if (strlen($hex) === 6 && ctype_xdigit($hex)) {
                    $rl = hexdec(substr($hex, 0, 2)) / 255;
                    $gl = hexdec(substr($hex, 2, 2)) / 255;
                    $bl = hexdec(substr($hex, 4, 2)) / 255;
                    $rl = $rl <= 0.03928 ? $rl / 12.92 : pow(($rl + 0.055) / 1.055, 2.4);
                    $gl = $gl <= 0.03928 ? $gl / 12.92 : pow(($gl + 0.055) / 1.055, 2.4);
                    $bl = $bl <= 0.03928 ? $bl / 12.92 : pow(($bl + 0.055) / 1.055, 2.4);
                    $fgColor = (0.2126 * $rl + 0.7152 * $gl + 0.0722 * $bl) > 0.179 ? '#000' : '#fff';
                }
                $style = 'background:' . $color . ';color:' . $fgColor . ';';
            }
            $sel      = (string)($ticket->milestoneid ?? '') === (string)$milestone->id ? 'selected' : '';
            $chipHtml = '<span class="chip-badge state-default" style="' . $style . '"><span class="chip-icon material-symbols-rounded">label_important</span>' . e($milestone->headline) . '</span>';
        @endphp
        <option value="{{ $milestone->id }}" {{ $sel }} data-chip-html="{{ $chipHtml }}">{{ e($milestone->headline) }}</option>
    @endforeach
</x-globals::forms.select>
