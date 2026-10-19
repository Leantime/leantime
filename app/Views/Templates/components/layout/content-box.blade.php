{{--
    layout.content-box — the page's content box (.maincontentinner), optionally split into a left
    sidebar, the main content and a right sidebar. Sidebars sit on a light grey background and are
    resizable: drag the divider, or focus it and use the arrow keys (Home / End: narrowest / widest,
    double-click: default width). With an `id` the widths are remembered per user, in the same
    store as accordion states.

    Without sidebars it renders the plain content box, so every `.maincontentinner` style keeps
    applying. Below 992px (or 1200px with stack="lg") the sidebars stack above / below the main
    content, and the sidebars give up width before the main pane drops under 320px.

    Props:
      id          string  The box's HTML id; also remembers the sidebar widths per user (UiState:
                          "content-box_{id}_left|right").
      leftWidth   int     Default width of the left sidebar in px (260).
      rightWidth  int     Default width of the right sidebar in px (300).
      resizable   bool    Show the resize handles (default true).
      stack       string  Below which width the sidebars stack: 'md' (992px, default) or 'lg' (1200px,
                          for pages whose main pane is hidden on smaller screens anyway).
      mainClass   string  Classes for the main pane (e.g. "hidden-sm" to hide it on small screens).

    Slots:
      default  The main content.
      left     Left sidebar (optional). Slot attributes (class, id …) go on the sidebar.
      right    Right sidebar (optional).

    Anything else (class, data-*, style) passes through to the box.
--}}
@props([
    'id' => null,
    'leftWidth' => 260,
    'rightWidth' => 300,
    'resizable' => true,
    'stack' => 'md',
    'mainClass' => '',
])

@php
    $hasLeft = isset($left) && trim((string) $left) !== '';
    $hasRight = isset($right) && trim((string) $right) !== '';

    // Widths are saved per user in UiState (one row each); the id escapes like elements.accordion's.
    $stateId = $id === null ? null : preg_replace_callback('/[^A-Za-z0-9_-]/', fn ($m) => '~' . bin2hex($m[0]), (string) $id);
    $minWidth = 160;
    $maxWidth = 800;
    $widthFor = function (string $side, int $default) use ($stateId, $minWidth, $maxWidth): int {
        $saved = $stateId === null ? null : app(\Leantime\Core\UI\UiState::class)->get('content-box_' . $stateId . '_' . $side);

        return is_numeric($saved) ? max($minWidth, min($maxWidth, (int) $saved)) : $default;
    };
    $leftW = $hasLeft ? $widthFor('left', (int) $leftWidth) : (int) $leftWidth;
    $rightW = $hasRight ? $widthFor('right', (int) $rightWidth) : (int) $rightWidth;
    $htmlId = $id === null ? [] : ['id' => $id];
@endphp

@if (! $hasLeft && ! $hasRight)
    <div {{ $attributes->class(['maincontentinner'])->merge($htmlId) }}>
        {{ $slot }}
    </div>
@else
    <div {{ $attributes->class(['maincontentinner', 'lt-content', 'lt-content--stack-lg' => $stack === 'lg'])->merge($htmlId + [
            'style' => ($hasLeft ? '--lt-content-left-w: ' . $leftW . 'px;' : '') . ($hasRight ? ' --lt-content-right-w: ' . $rightW . 'px;' : ''),
        ]) }} @if ($stateId !== null) data-lt-content="{{ $stateId }}" @endif>
        @if ($hasLeft)
            <aside {{ $left->attributes->class(['lt-content-side', 'lt-content-side--left']) }}>{{ $left }}</aside>
            @if ($resizable)
                <div class="lt-content-resizer" role="separator" tabindex="0" aria-orientation="vertical"
                     aria-label="{{ __('label.resize_sidebar') }}" aria-valuenow="{{ $leftW }}"
                     aria-valuemin="{{ $minWidth }}" aria-valuemax="{{ $maxWidth }}"
                     data-lt-side="left" data-lt-default="{{ (int) $leftWidth }}"></div>
            @endif
        @endif
        <div @class(['lt-content-main', $mainClass => $mainClass !== ''])>
            {{ $slot }}
        </div>
        @if ($hasRight)
            @if ($resizable)
                <div class="lt-content-resizer" role="separator" tabindex="0" aria-orientation="vertical"
                     aria-label="{{ __('label.resize_sidebar') }}" aria-valuenow="{{ $rightW }}"
                     aria-valuemin="{{ $minWidth }}" aria-valuemax="{{ $maxWidth }}"
                     data-lt-side="right" data-lt-default="{{ (int) $rightWidth }}"></div>
            @endif
            <aside {{ $right->attributes->class(['lt-content-side', 'lt-content-side--right']) }}>{{ $right }}</aside>
        @endif
    </div>
@endif
