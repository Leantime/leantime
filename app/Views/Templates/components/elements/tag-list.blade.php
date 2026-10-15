{{--
    elements.tag-list — read-only tags as chips (kanban card, table cell, idea card).

    Same chip size as every badge (20px, 12px / semibold); read-only, so the soft-tint tone.
    Editing tags is forms.tags.

    Props:
      tags  string|array  Comma-separated string or a list of tags.

    Anything else passes through to the wrapper.
--}}
@props([
    'tags' => [],
])

@php
    $tagList = is_array($tags) ? $tags : explode(',', (string) $tags);
    $tagList = array_values(array_filter(array_map('trim', $tagList), 'strlen'));
@endphp

@if (count($tagList) > 0)
    <span {{ $attributes->class(['lt-tag-list']) }}>
        @foreach ($tagList as $tag)
            <span class="lt-tag">{{ $tag }}</span>
        @endforeach
    </span>
@endif
