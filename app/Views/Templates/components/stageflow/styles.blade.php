{{--
    Stageflow component styles.
    Include once per page: @include('global::components.stageflow.styles')

    These styles power the stage-flow layout used by Logic Model and
    other blueprint boards. Stage cards use inline CSS custom properties
    (--stage-color, --stage-bg) set via the <x-global::stageflow.card>
    component.
--}}
@once
<style>
/* ═══════════════════════════════════════════════════════
   Stageflow — Reusable stage-flow layout
   ═══════════════════════════════════════════════════════ */

/* ── Flow container ── */
.sf-flow {
    display: flex;
    align-items: stretch;
    gap: 8px;
    padding: 8px 0;
}

/* ── Stage card ── */
.sf-stage {
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
.sf-stage:not(.active) {
    cursor: pointer;
}
.sf-stage:not(.active):hover {
    box-shadow: var(--regular-shadow);
}
.sf-stage.active {
    z-index: 10;
    box-shadow: var(--large-shadow);
    border-color: transparent;
    background: linear-gradient(180deg, var(--stage-bg) 0%, var(--secondary-background) 100px);
}

/* ── Current Focus flag ── */
.sf-flag {
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
.sf-stage.active .sf-flag { opacity: 1; }

/* ── Stage header ── */
.sf-hd {
    padding: 14px 10px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    border-bottom: 2px solid var(--main-border-color);
    transition: border-color 300ms, padding 300ms;
    position: relative;
}
.sf-stage.active .sf-hd {
    padding-top: 18px;
    border-bottom-width: 3px;
    border-bottom-color: var(--stage-color);
}

/* Icon box */
.sf-icon {
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
.sf-stage:not(.active) .sf-icon {
    width: 30px;
    height: 30px;
    font-size: var(--font-size-m);
    margin-bottom: 5px;
}
.sf-stage.active .sf-icon {
    background: var(--stage-color);
    color: white;
}

/* Title row with count badge */
.sf-title-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin-bottom: 2px;
}
.sf-name {
    font-size: var(--font-size-l);
    font-weight: 700;
    transition: font-size 300ms;
    line-height: 1.2;
    color: var(--primary-font-color);
}
.sf-stage:not(.active) .sf-name {
    font-size: var(--font-size-m);
    font-weight: 600;
    opacity: 0.5;
}

/* Count — inline with title, kanban style */
.sf-count {
    font-size: var(--font-size-l);
    font-weight: 700;
    line-height: 1.2;
}
.sf-stage:not(.active) .sf-count {
    font-size: var(--font-size-m);
    opacity: 0.4;
}

/* Subtitle */
.sf-sub {
    font-size: var(--font-size-xs);
    color: var(--primary-font-color);
    opacity: 0.6;
    transition: font-size 300ms;
    text-align: center;
}
.sf-stage:not(.active) .sf-sub {
    font-size: var(--font-size-xs);
    opacity: 0.5;
}

/* ── Stage body ── */
.sf-body {
    padding: 8px 8px 8px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
}

/* ── Items ── */
.sf-item {
    padding: 7px 10px;
    border-radius: var(--box-radius-small);
    cursor: pointer;
    transition: background 150ms, border-color 150ms;
    border-left: 3px solid transparent;
    position: relative;
}
.sf-item:hover { background: rgba(0,0,0,0.02); }
.sf-stage.active .sf-item { border-left-color: var(--stage-color); }

/* Item title */
.sf-item-title {
    font-size: var(--font-size-s);
    font-weight: 600;
    line-height: 1.3;
    color: var(--primary-font-color);
    display: flex;
    align-items: center;
    gap: 4px;
    overflow: hidden;
}
.sf-item-title a,
.sf-item-title span:not(.sf-dot) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.sf-item-title a {
    color: var(--primary-font-color);
    text-decoration: none;
}
.sf-item-title a:hover { color: var(--accent1); }

/* Status dot */
.sf-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    display: inline-block;
    vertical-align: middle;
    margin-right: 6px;
    position: relative;
    top: -1px;
}
.sf-dot--blue { background: #1B75BB; }
.sf-dot--orange { background: #fdab3d; }
.sf-dot--green { background: #75BB1B; }
.sf-dot--red { background: #BB1B25; }
.sf-dot--grey { background: #c3ccd4; }

/* Item description */
.sf-item-desc {
    font-size: var(--font-size-xs);
    color: var(--primary-font-color);
    opacity: 0.6;
    line-height: 1.4;
    margin-top: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
}

/* Item footer */
.sf-item-foot {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
    flex-wrap: wrap;
}
.sf-meta {
    font-size: var(--font-size-xs);
    color: var(--primary-font-color);
    opacity: 0.5;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.sf-meta i { font-size: var(--font-size-xs); }
.sf-meta a { color: inherit; text-decoration: none; }
.sf-meta a:hover { color: var(--accent1); opacity: 1; }

.sf-avatar {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    vertical-align: middle;
}

/* ── Inactive stage: compact view ── */
.sf-stage:not(.active) .sf-item {
    padding: 4px 8px;
    border-left-color: transparent;
}
.sf-stage:not(.active) .sf-item-title {
    font-size: var(--font-size-s);
    font-weight: 500;
    color: var(--primary-font-color);
    opacity: 1;
    display: flex;
    align-items: center;
}
.sf-stage:not(.active) .sf-item-title .sf-dot {
    width: 6px;
    height: 6px;
    margin-right: 4px;
}
.sf-stage:not(.active) .sf-item-desc { display: none; }
.sf-stage:not(.active) .sf-item-foot { display: none; }
.sf-stage:not(.active) .sf-item .inlineDropDownContainer { display: none; }

/* Disable interactive elements in inactive stages */
.sf-stage:not(.active) a,
.sf-stage:not(.active) button,
.sf-stage:not(.active) .dropdown-toggle {
    pointer-events: none;
}

/* ── Empty state ── */
.sf-empty {
    text-align: center;
    padding: 16px 8px;
    font-size: var(--font-size-xs);
    color: var(--primary-font-color);
    opacity: 0.5;
}
.sf-empty-icon {
    font-size: var(--font-size-xl);
    opacity: 0.3;
    margin-bottom: 6px;
    display: block;
}
.sf-stage:not(.active) .sf-empty { display: none; }

/* ── Add item button ── */
.sf-add {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 6px;
    color: var(--primary-font-color);
    opacity: 0.5;
    font-size: var(--font-size-xs);
    font-weight: 500;
    cursor: pointer;
    background: transparent;
    text-decoration: none !important;
    transition: all 150ms;
    margin-top: auto;
    border: none;
    border-radius: var(--box-radius-small);
}
.sf-add:hover {
    opacity: 1;
    color: var(--accent1);
    background: rgba(0,69,110,0.04);
}
.sf-stage:not(.active) .sf-add { display: none; }

/* ── Responsive ── */
@media (max-width: 1100px) {
    .sf-flow { flex-wrap: wrap; gap: 8px; }
    .sf-stage { flex: 1 1 calc(50% - 4px); min-width: 160px; }
}

/* ── Status pill (plugin enhancement) ── */
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

/* ── Project link icon (plugin enhancement) ── */
.sf-project-link-icon i {
    color: var(--accent1);
    opacity: 0.7;
}

/* ── Print ── */
@media print {
    @page { size: landscape; margin: 0.5in; }

    .sf-stage .sf-item-desc { display: block !important; }
    .sf-stage .sf-item-foot { display: flex !important; }
    .sf-stage .sf-add { display: none !important; }
    .sf-flag { display: none !important; }
    .sf-item-actions { display: none !important; }
    .sf-health-badge { display: none !important; }

    .sf-flow {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }
    .sf-stage {
        min-width: unset !important;
        max-width: unset !important;
        flex: 0 0 calc(33.33% - 8px) !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }
    /* Force page break after the 3rd stage */
    .sf-stage:nth-child(3) {
        break-after: page;
        page-break-after: always;
    }
    .sf-stage:nth-child(n+4) {
        flex: 0 0 calc(50% - 6px) !important;
    }
}
</style>
@endonce
