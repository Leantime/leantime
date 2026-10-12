{{--
    actions.modal-header — the first row of every dialog.

    Lines up with the modal's close X (rendered by nyroModal outside the content, pinned 35px
    down / 40px in, 25px tall): the row is 25px high, starts at the X's top and keeps room on
    the right so nothing runs under it. Tabs, if any, go in their own row below.

    Props:
      icon    string  Optional Font Awesome classes for the leading icon ("fa-solid fa-bullseye").
      title   string  The dialog's title (escaped). For markup (a translation with an icon, a
                      parent breadcrumb) use the `title` slot instead.

    Slots:
      title    Title with markup.
      meta     Optional quiet line on the right ("Created by … | Last updated …").
      actions  Optional menu items (<li>…</li>) for the ⋮ menu. Destructive actions (Delete)
               belong here, never in the dialog body.

    Anything else passes through to the header element.
--}}
@props([
    'icon' => null,
    'title' => null,
])

<div {{ $attributes->class(['lt-modal-header']) }}>
    <h4 class="lt-modal-title">
        @if ($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif
        {{ $title }}
    </h4>

    @isset($meta)
        <div class="lt-modal-meta">{{ $meta }}</div>
    @endisset

    @isset($actions)
        <x-global::actions.dropdown :aria-label="__('label.actions')" class="lt-modal-actions">
            {{ $actions }}
        </x-global::actions.dropdown>
    @endisset
</div>
