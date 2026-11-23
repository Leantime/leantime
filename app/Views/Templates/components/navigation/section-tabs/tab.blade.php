{{--
    One tab in x-global::navigation.section-tabs.

    Props:
      for    string  REQUIRED. Id of the panel this tab opens.
      icon   string  Optional Font Awesome classes (e.g. "fa-building", "fa-solid fa-key").

    Other attributes (id, data-*) land on the link.
--}}
@props([
    'for',
    'icon' => null,
])

<li><a {{ $attributes->merge(['href' => '#'.$for]) }}>@if ($icon)<span class="fa {{ $icon }}" aria-hidden="true"></span> @endif{{ $slot }}</a></li>
