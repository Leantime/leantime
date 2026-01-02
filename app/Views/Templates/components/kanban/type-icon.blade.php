@props([
    'type' => 'task',
    'size' => 'md'
])

@php
use Leantime\Domain\Tickets\Models\TicketDesignTokens;

$token = TicketDesignTokens::getType($type);
$icon = $token['icon'] ?? '📋';
$label = $token['label'] ?? 'Task';

$sizes = ['sm' => '14px', 'md' => '16px', 'lg' => '20px'];
$fontSize = $sizes[$size] ?? '16px';
@endphp

<span {{ $attributes->merge(['class' => 'type-icon']) }}
      style="font-size: {{ $fontSize }}; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; width: 24px;"
      title="Type: {{ $label }}"
      role="img"
      aria-label="Type: {{ $label }}">{{ $icon }}</span>
