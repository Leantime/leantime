{{--
    elements.board-column-header — the title bar on top of a board column: a To-Do kanban status,
    an idea board column, a Blueprint box.

    Renders today's `h4.widgettitle.title-primary` bar (kanban.css), so boards look the same. The
    kanban scripts read the count by its class (`.widgettitle .count`) and the tour points at the
    menu in it, so the class names are part of the contract.

    Props:
      icon         string    Font Awesome classes for a leading icon, e.g. 'fas fa-lock'.
      count        int|null  Number shown before the title (kanban columns; their script rewrites it).
      statusClass  string    The column's status colour class (`label-info`, `label-purple` …): a
                             coloured bottom border, and the count in that colour.
      align        string    'start' (default) or 'center' (Blueprint section and label rows).

    Slots:
      default  The title. Markup is allowed (translations with markup, `&nbsp;` for an empty bar).
      actions  The column's menu or edit link, floated right.

    Anything else (id, data-*, extra classes) passes through to the heading.
--}}
@props([
    'icon' => null,
    'count' => null,
    'statusClass' => null,
    'align' => 'start',
])

<h4 {{ $attributes->class([
    'widgettitle',
    'title-primary',
    'center' => $align === 'center',
    'title-border-'.$statusClass => (bool) $statusClass,
]) }}>
    @isset($actions)
        {{ $actions }}
    @endisset
    @if ($count !== null)
        <strong class="count">{{ $count }}</strong>
    @endif
    @if ($icon)
        <i class="{{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</h4>
