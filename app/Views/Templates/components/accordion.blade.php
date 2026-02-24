{{-- Backward-compat wrapper: maps old API → elements.accordion --}}
@props([
    'state' => $tpl->getToggleState("accordion_content-".$id) == 'closed' ? 'closed' : 'open',
    'id'
])

<x-globals::elements.accordion :state="$state" :id="$id" {{ $attributes }}>
    <x-slot:title {{ $title->attributes }}>{{ $title }}</x-slot:title>
    <x-slot:content {{ $content->attributes }}>{{ $content }}</x-slot:content>
    @if(isset($actionlink) && $actionlink != '')
        <x-slot:actionlink>{{ $actionlink }}</x-slot:actionlink>
    @endif
</x-globals::elements.accordion>
