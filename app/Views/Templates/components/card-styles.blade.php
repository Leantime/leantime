{{--
    Card styles (lt-card). Token-driven, so themeable light/dark automatically.
    Self-included via @once from card.blade.php — never include it by hand.

    This file holds the card's own, context-free look. How a card restyles when
    it sits inside an active/inactive <x-global::column> lives in
    column-styles.blade.php (those rules are column-state driven).
--}}
@once
<style>
/* ═══════════════════════════════════════════════════════
   Card — reusable content tile
   ═══════════════════════════════════════════════════════ */
.lt-card {
    padding: 7px 10px;
    border-radius: var(--box-radius-small);
    cursor: pointer;
    transition: background 150ms, border-color 150ms;
    border-left: 3px solid transparent;
    position: relative;
}
.lt-card:hover { background: rgba(0,0,0,0.02); }

/* Title */
.lt-card-title {
    font-size: var(--font-size-s);
    font-weight: 600;
    line-height: 1.3;
    color: var(--primary-font-color);
    display: flex;
    align-items: center;
    gap: 4px;
    overflow: hidden;
}
.lt-card-title a,
.lt-card-title span:not(.lt-card-dot) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.lt-card-title a {
    color: var(--primary-font-color);
    text-decoration: none;
}
.lt-card-title a:hover { color: var(--accent1); }

/* Status dot */
.lt-card-dot {
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
.lt-card-dot--blue { background: #1B75BB; }
.lt-card-dot--orange { background: #fdab3d; }
.lt-card-dot--green { background: #75BB1B; }
.lt-card-dot--red { background: #BB1B25; }
.lt-card-dot--grey { background: #c3ccd4; }

/* Description */
.lt-card-desc {
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

/* Footer */
.lt-card-foot {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
    flex-wrap: wrap;
}
.lt-card-meta,
.sf-meta {
    font-size: var(--font-size-xs);
    color: var(--primary-font-color);
    opacity: 0.5;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.lt-card-meta i,
.sf-meta i { font-size: var(--font-size-xs); }
.lt-card-meta a,
.sf-meta a { color: inherit; text-decoration: none; }
.lt-card-meta a:hover,
.sf-meta a:hover { color: var(--accent1); opacity: 1; }

.lt-card-avatar {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    vertical-align: middle;
}
</style>
@endonce
