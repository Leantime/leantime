@props([
    'name',
    'options' => [],
    'labelText' => '',
    'caption' => '',
    'validationText' => '',
    'validationState' => '',
    'trailingVisual' => '',
    'leadingVisual' => '',
    'state' => '',
    'variant' => 'single',
    'selected' => [],
])

<div>
    @if($labelText)
        <label for="{{ $name }}">
            <span class="label-text">{{ $labelText }}</span>
        </label>
    @endif

    <div>
        @if($leadingVisual)
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="{{ $leadingVisual }}"></i>
            </span>
        @endif

        <select 
            name="{{ $name }}" 
            id="{{ $name }}" 
            @class([
                'select select-bordered',
            ])
            {{ $state === 'disabled' ? 'disabled' : '' }}
            {{-- {{ $variant === 'multiple' || $variant === 'tags' ? 'multiple' : '' }} --}}
        >
            @foreach($options as $value => $label)
                <option value="{{ $value }}" {{ in_array($value, (array)$selected) ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @if($trailingVisual)
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="{{ $trailingVisual }}"></i>
            </span>
        @endif
    </div>

    @if($caption)
        <label>
            <span class="label-text-alt">{{ $caption }}</span>
        </label>
    @endif

    {{-- @if($validationText)
        <label>
            <span class="label-text-alt text-{{ $validationState }}">{{ $validationText }}</span>
        </label>
    @endif --}}
</div>