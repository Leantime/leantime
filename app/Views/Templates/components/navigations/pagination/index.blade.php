@props([
    'contents',
    'inputType' => 'button',
    'outline' => false,
    'scale' => ''
])

<div class="join">
    {{ $contents->withAttributes(['inputType' => $inputType, 'outline' => $outline, 'scale' => $scale]) }}
</div>