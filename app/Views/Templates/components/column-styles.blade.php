{{--
    Column styles (lt-column). Token-driven, themeable light/dark.
    Self-included via @once from column.blade.php — never include it by hand.

    Includes the column-context overrides for cards (how a <x-global::card>
    compacts inside an inactive column / picks up the accent inside an active
    one), plus a few sf-* rules the StrategyPro plugin emits or relies on
    (.sf-status-pill, .sf-project-link-icon, and print rules hiding the
    plugin-injected .sf-item-actions / .sf-health-badge) for the migration window.
--}}
@once
<style>
/* ═══════════════════════════════════════════════════════
   Board flow — row of columns
   ═══════════════════════════════════════════════════════ */
.lt-board {
    display: flex;
    align-items: stretch;
    gap: 8px;
    padding: 8px 0;
}

/* ═══════════════════════════════════════════════════════
   Column — board lane
   ═══════════════════════════════════════════════════════ */
.lt-column {
    flex: 1 1 0;
    min-width: 0;
    display: flex;
    flex-direction: column;
    border-radius: var(--box-radius);
    background: var(--secondary-background);
    transition: box-shadow 350ms cubic-bezier(0.25, 0.46, 0.45, 0.94),
                background 350ms cubic-bezier(0.25, 0.46, 0.45, 0.94),
                border-color 350ms cubic-bezier(0.25, 0.46, 0.45, 0.94);
    position: relative;
    box-shadow: var(--min-shadow);
    border: 1px solid var(--main-border-color);
    overflow: visible;
    z-index: 1;
}
.lt-column:not(.lt-column--active) {
    cursor: pointer;
}
.lt-column:not(.lt-column--active):hover {
    box-shadow: var(--regular-shadow);
}
.lt-column--active {
    z-index: 10;
    box-shadow: var(--large-shadow);
    border-color: transparent;
    background: linear-gradient(180deg, var(--stage-bg) 0%, var(--secondary-background) 100px);
}

/* ── Focus flag ── */
.lt-column-flag {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: white;
    z-index: 11;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 250ms;
    box-shadow: var(--regular-shadow);
}
.lt-column--active .lt-column-flag { opacity: 1; }
/* No focus label → don't render an empty pill. */
.lt-column-flag:empty { display: none; }

/* ── Header ── */
.lt-column-header {
    padding: 14px 10px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    border-bottom: 2px solid var(--main-border-color);
    transition: border-color 300ms, padding 300ms;
    position: relative;
}
.lt-column--active .lt-column-header {
    padding-top: 18px;
    border-bottom-width: 3px;
    border-bottom-color: var(--stage-color);
}

/* Icon box */
.lt-column-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--element-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-m);
    margin-bottom: 6px;
    transition: background 300ms, color 300ms, width 300ms, height 300ms;
    background: var(--stage-bg);
    color: var(--stage-color);
}
.lt-column:not(.lt-column--active) .lt-column-icon {
    width: 30px;
    height: 30px;
    font-size: var(--font-size-m);
    margin-bottom: 5px;
}
.lt-column--active .lt-column-icon {
    background: var(--stage-color);
    color: white;
}

/* Title row + count */
.lt-column-title-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin-bottom: 2px;
}
.lt-column-title {
    font-size: var(--font-size-l);
    font-weight: 700;
    transition: font-size 300ms;
    line-height: 1.2;
    color: var(--primary-font-color);
}
.lt-column:not(.lt-column--active) .lt-column-title {
    font-size: var(--font-size-m);
    font-weight: 600;
    opacity: 0.5;
}

/* Count — inline with title, kanban style */
.lt-column-count {
    font-size: var(--font-size-l);
    font-weight: 700;
    line-height: 1.2;
}
.lt-column:not(.lt-column--active) .lt-column-count {
    font-size: var(--font-size-m);
    opacity: 0.4;
}

/* Subtitle */
.lt-column-sub {
    font-size: var(--font-size-xs);
    color: var(--primary-font-color);
    opacity: 0.6;
    transition: font-size 300ms;
    text-align: center;
}
.lt-column:not(.lt-column--active) .lt-column-sub {
    font-size: var(--font-size-xs);
    opacity: 0.5;
}

/* ── Body ── */
.lt-column-body {
    padding: 8px 8px 8px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
}

/* ═══════════════════════════════════════════════════════
   Card behaviour inside a column (column-state driven)
   ═══════════════════════════════════════════════════════ */
.lt-column--active .lt-card { border-left-color: var(--stage-color); }

.lt-column:not(.lt-column--active) .lt-card {
    padding: 4px 8px;
    border-left-color: transparent;
}
.lt-column:not(.lt-column--active) .lt-card-title {
    font-size: var(--font-size-s);
    font-weight: 500;
    color: var(--primary-font-color);
    opacity: 1;
    display: flex;
    align-items: center;
}
.lt-column:not(.lt-column--active) .lt-card-title .lt-card-dot {
    width: 6px;
    height: 6px;
    margin-right: 4px;
}
.lt-column:not(.lt-column--active) .lt-card-desc { display: none; }
.lt-column:not(.lt-column--active) .lt-card-foot { display: none; }
.lt-column:not(.lt-column--active) .lt-card .inlineDropDownContainer { display: none; }

/* Disable interactive elements in inactive columns */
.lt-column:not(.lt-column--active) a,
.lt-column:not(.lt-column--active) button,
.lt-column:not(.lt-column--active) .dropdown-toggle {
    pointer-events: none;
}

/* ── Empty state ── */
.lt-column-empty {
    text-align: center;
    padding: 16px 8px;
    font-size: var(--font-size-xs);
    color: var(--primary-font-color);
    opacity: 0.5;
}
.lt-column-empty-icon {
    font-size: var(--font-size-xl);
    opacity: 0.3;
    margin-bottom: 6px;
    display: block;
}
.lt-column:not(.lt-column--active) .lt-column-empty { display: none; }

/* ── Add item button ── */
.lt-column-add {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 6px;
    color: var(--primary-font-color);
    opacity: 0.5;
    font-size: var(--base-font-size);
    font-weight: 500;
    cursor: pointer;
    background: transparent;
    text-decoration: none !important;
    transition: all 150ms;
    margin-top: auto;
    border: none;
    border-radius: var(--box-radius-small);
}
.lt-column-add:hover {
    opacity: 1;
    color: var(--accent1);
    background: rgba(0,69,110,0.04);
}
.lt-column:not(.lt-column--active) .lt-column-add { display: none; }

/* ── Responsive ── */
@media (max-width: 1100px) {
    .lt-board { flex-wrap: wrap; gap: 8px; }
    .lt-column { flex: 1 1 calc(50% - 4px); min-width: 160px; }
}

/* ═══════════════════════════════════════════════════════
   StrategyPro plugin enhancements (sf-* names emitted by the plugin)
   ═══════════════════════════════════════════════════════ */
.sf-status-pill {
    display: inline-block;
    padding: 0 6px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 600;
    line-height: 1.7;
    white-space: nowrap;
    letter-spacing: 0.2px;
}
.sf-project-link-icon i {
    color: var(--accent1);
    opacity: 0.7;
}

/* ── Print ── */
@media print {
    @page { size: landscape; margin: 0.5in; }

    .lt-column .lt-card-desc { display: block !important; }
    .lt-column .lt-card-foot { display: flex !important; }
    .lt-column .lt-column-add { display: none !important; }
    .lt-column-flag { display: none !important; }
    .sf-item-actions { display: none !important; }
    .sf-health-badge { display: none !important; }

    .lt-board {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }
    .lt-column {
        min-width: unset !important;
        max-width: unset !important;
        flex: 0 0 calc(33.33% - 8px) !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }
    /* Force page break after the 3rd column */
    .lt-column:nth-child(3) {
        break-after: page;
        page-break-after: always;
    }
    .lt-column:nth-child(n+4) {
        flex: 0 0 calc(50% - 6px) !important;
    }
}
</style>
@endonce
