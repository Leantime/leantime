{{--
    actions.modal-header — the first row of every dialog.

    Owns the dialog's close X, so title, ⋮ and X always share one row. nyroModal's own X is
    pinned outside the content with inline positions and does not line up reliably (mobile
    especially); when a dialog has this header, that X is hidden (nyroModal.css). Tabs, if any,
    go in their own row below.

    Props:
      icon    string  Optional Font Awesome classes for the leading icon ("fa-solid fa-bullseye").
      title   string  The dialog's title (escaped). For markup (a translation with an icon, a
                      parent breadcrumb) use the `title` slot instead.
      eyebrow     string  Small uppercase label above the title ("New allocation", "Program").
      description string  One muted line under the title.

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
    'eyebrow' => null,
    'description' => null,
])

<div {{ $attributes->class(['lt-modal-header']) }}>
    @if ($eyebrow || $description)
        <div class="lt-modal-heading">
            @if ($eyebrow)
                <div class="lt-modal-eyebrow">{{ $eyebrow }}</div>
            @endif
            <h4 class="lt-modal-title">
                @if ($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif
                {{ $title }}
            </h4>
            @if ($description)
                <p class="lt-modal-desc">{{ $description }}</p>
            @endif
        </div>
    @else
        <h4 class="lt-modal-title">
            @if ($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif
            {{ $title }}
        </h4>
    @endif

    @isset($meta)
        <div class="lt-modal-meta">{{ $meta }}</div>
    @endisset

    @isset($actions)
        <x-global::actions.dropdown :aria-label="__('label.actions')" class="lt-modal-actions">
            {{ $actions }}
        </x-global::actions.dropdown>
    @endisset

    {{-- Closes through nmTop() (like lt:ui:modal.close), so it keeps working after htmx
         re-renders the dialog content; nyroModal only binds .nyroModalClose once. --}}
    <button type="button" class="lt-modal-close" aria-label="{{ __('buttons.close') }}"
            onclick="jQuery.nmTop() && jQuery.nmTop().close();">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
</div>
