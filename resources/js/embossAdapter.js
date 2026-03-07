/**
 * embossAdapter.js — Leantime ↔ Emboss bridge
 *
 * Converts Leantime's date-string-based task format into Emboss's
 * day-offset Row format, and wires Emboss events back to Leantime's
 * existing persistence APIs.
 *
 * Usage (from controller):
 *   leantime.embossAdapter.init('#gantt', leantimeTasks, {
 *       viewMode: 'month',
 *       readonly: false,
 *       viewSettingKey: 'roadmap',
 *       entityType: 'ticket',
 *   });
 */

import { Emboss } from '@emboss-js/core'
import { todayMarker, tooltips, dependencyArrows } from '@emboss-js/core/extensions/free'
import { sidebar, phases, milestones, inlineEdit } from '@emboss-js/core/extensions/organize'
import { columns } from '@emboss-js/core/extensions/columns'

// Emboss themes — grayscale for structure, vivid for colorful fills
import '@emboss-js/core/themes/grayscale.css'
import '@emboss-js/core/extensions/organize/vivid.css'

var leantime = window.leantime || (window.leantime = {});

leantime.embossAdapter = (function () {

    // ── Date helpers ─────────────────────────────────────────────────────

    /**
     * Parse a date string (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS) into a
     * midnight-normalised Date object.
     */
    function parseDate(str) {
        var d = new Date(str);
        d.setHours(0, 0, 0, 0);
        return d;
    }

    /** Whole days between two Date objects. */
    function daysBetween(a, b) {
        return Math.round((b.getTime() - a.getTime()) / 86400000);
    }

    /** Add days to a Date, returning a new Date. */
    function addDays(date, days) {
        var d = new Date(date);
        d.setDate(d.getDate() + days);
        return d;
    }

    /** Format a Date as YYYY-MM-DD HH:MM:SS (UTC-style, matching DB format). */
    function toSQL(date) {
        var y = date.getFullYear();
        var m = String(date.getMonth() + 1).padStart(2, '0');
        var d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d + ' 00:00:00';
    }

    // ── Phase color resolver ────────────────────────────────────────────
    // Default grey (#8D99A6) and empty values fall back to Leantime's accent2.

    // Leantime Mint Leaf — hardcoded to avoid CSS variable parsing issues
    var LEANTIME_ACCENT2 = '#00b893';

    // Colors that should be treated as "no color set" and replaced with accent2
    var DEFAULT_COLORS = ['#8D99A6', '#124F7D'];

    function resolvePhaseColor(bgColor) {
        if (!bgColor || bgColor.trim() === '' || bgColor === 'null') {
            return LEANTIME_ACCENT2;
        }
        // CSS variable strings (e.g. "var(--grey)") aren't real colors for Emboss
        if (bgColor.indexOf('var(') === 0) {
            return LEANTIME_ACCENT2;
        }
        // Replace old default/system colors with Leantime accent
        if (DEFAULT_COLORS.indexOf(bgColor.toUpperCase()) !== -1 || DEFAULT_COLORS.indexOf(bgColor) !== -1) {
            return LEANTIME_ACCENT2;
        }
        return bgColor;
    }

    // ── View mode mapping ────────────────────────────────────────────────
    // Leantime uses capitalised names ('Day', 'Week', 'Month');
    // Emboss uses lowercase ('day', 'week', 'month', 'quarter').

    var VIEW_MAP = { 'Day': 'day', 'Week': 'week', 'Month': 'month', 'Quarter': 'quarter' };
    var VIEW_MAP_REVERSE = { 'day': 'Day', 'week': 'Week', 'month': 'Month', 'quarter': 'Quarter' };

    // ── Leantime task → Emboss Row conversion ────────────────────────────

    /**
     * Compute the project start date (earliest date across all tasks,
     * minus a 7-day buffer so bars don't start at pixel 0).
     */
    function computeProjectStart(tasks) {
        var earliest = null;
        for (var i = 0; i < tasks.length; i++) {
            var d = parseDate(tasks[i].start);
            if (!earliest || d < earliest) earliest = d;
        }
        if (!earliest) earliest = new Date();
        return addDays(earliest, -7);
    }

    /**
     * Map a single Leantime task object to an Emboss Row.
     *
     * Leantime format:
     *   { id, name, start: 'YYYY-MM-DD', end: 'YYYY-MM-DD', progress,
     *     dependencies: 'id1,id2', type: 'milestone'|'task',
     *     bg_color, thumbnail, sortIndex, projectName }
     *
     * Emboss Row format:
     *   { id, type, name, start (dayOffset), duration (days), progress,
     *     depth, parentId, collapsed, hidden, status, dependencies: [] }
     */
    function toRow(task, projectStart) {
        var startDate = parseDate(task.start);
        var endDate = parseDate(task.end);
        var dayOffset = daysBetween(projectStart, startDate);
        // Minimum 7 days so bars render as visible pills, not circles, at any zoom
        var duration = Math.max(7, daysBetween(startDate, endDate));

        // Parse dependencies: Leantime uses comma-separated string, Emboss uses array
        var deps = [];
        if (task.dependencies && task.dependencies !== '') {
            deps = String(task.dependencies).split(',').map(function (d) { return d.trim(); }).filter(Boolean);
        }

        var progress = parseFloat(task.progress) || 0;

        // Determine Emboss type
        var type = 'task';
        if (task.type === 'milestone') {
            type = 'phase';  // Milestones become collapsible phases in Emboss
        } else if (task.type === 'subtask') {
            type = 'subtask';
        }

        // Determine depth and parentId from Leantime's milestone hierarchy
        var depth = 0;
        var parentId = null;
        if (task.type !== 'milestone') {
            // Use milestoneid (most reliable) or fall back to dependencies
            if (task.milestoneid && String(task.milestoneid) !== '0' && String(task.milestoneid) !== '') {
                parentId = String(task.milestoneid);
                depth = 1;
            } else if (deps.length > 0) {
                parentId = deps[0];
                depth = 1;
            }
        }

        // Derive status from progress
        var status = 'upcoming';
        if (progress >= 100) status = 'done';
        else if (progress > 0) status = 'active';

        return {
            id: String(task.id),
            type: type,
            name: task.name || '',
            start: dayOffset,
            duration: duration,
            progress: progress,
            depth: depth,
            parentId: parentId,
            collapsed: false,
            hidden: false,
            status: status,
            dependencies: deps,

            // Phase color — used by Emboss vivid heuristics to color the whole row
            // User-set color takes priority; default to Leantime's --accent2 (Mint Leaf)
            phaseColor: resolvePhaseColor(task.bg_color),

            // Leantime-specific metadata preserved for event handlers
            _ltId: task.id,
            _ltType: task.type,
            _ltProjectName: task.projectName || null,
            _ltSortIndex: task.sortIndex,
            _ltThumbnail: task.thumbnail || null,
        };
    }

    /**
     * Generate a closing diamond milestone row for a Leantime milestone.
     * Placed after the milestone's children to complete the "sandwich".
     */
    function toDiamondRow(task, projectStart) {
        var endDate = parseDate(task.end);
        var dayOffset = daysBetween(projectStart, endDate);
        var progress = parseFloat(task.progress) || 0;

        var status = 'upcoming';
        if (progress >= 100) status = 'done';
        else if (progress > 0) status = 'active';

        return {
            id: String(task.id) + '-end',
            type: 'milestone',
            name: task.name || '',
            start: dayOffset,
            duration: 0,
            progress: progress,
            depth: 1,
            parentId: String(task.id),
            collapsed: false,
            hidden: false,
            status: status,
            dependencies: [String(task.id)],

            // Leantime metadata — links back to the same milestone
            _ltId: task.id,
            _ltType: task.type,
            _ltProjectName: task.projectName || null,
            _ltSortIndex: task.sortIndex,
            _ltThumbnail: task.thumbnail || null,
            _ltIsDiamond: true,
        };
    }

    /**
     * Convert an array of Leantime tasks to Emboss Rows.
     * Returns { rows: Row[], projectStart: Date }.
     *
     * Milestones become a "sandwich": phase bar (start→end) at the top,
     * child tasks in the middle, closing diamond at the end date.
     */
    function convertTasks(tasks) {
        var projectStart = computeProjectStart(tasks);
        var rows = [];

        // ── Group tasks by milestone ─────────────────────────────────────
        // Build a map: milestoneId → { milestone task, children[] }
        var milestones = {};    // id → task object
        var milestoneOrder = []; // preserve original order of milestones
        var childrenOf = {};    // milestoneId → [task, task, ...]
        var unassigned = [];    // tasks not assigned to any milestone

        for (var i = 0; i < tasks.length; i++) {
            var t = tasks[i];
            if (t.type === 'milestone') {
                var mid = String(t.id);
                milestones[mid] = t;
                milestoneOrder.push(mid);
                if (!childrenOf[mid]) childrenOf[mid] = [];
            }
        }

        for (var i = 0; i < tasks.length; i++) {
            var t = tasks[i];
            if (t.type === 'milestone') continue;

            // Find which milestone this task belongs to:
            // 1. Explicit milestoneid field (most reliable)
            // 2. dependencies field pointing to a milestone
            var parentMilestoneId = null;

            // Check milestoneid first
            if (t.milestoneid && String(t.milestoneid) !== '0' && String(t.milestoneid) !== '') {
                var mid = String(t.milestoneid);
                if (milestones[mid]) {
                    parentMilestoneId = mid;
                }
            }

            // Fallback: check dependencies
            if (!parentMilestoneId && t.dependencies && t.dependencies !== '') {
                var deps = String(t.dependencies).split(',');
                for (var j = 0; j < deps.length; j++) {
                    var depId = deps[j].trim();
                    if (milestones[depId]) {
                        parentMilestoneId = depId;
                        break;
                    }
                }
            }

            if (parentMilestoneId) {
                if (!childrenOf[parentMilestoneId]) childrenOf[parentMilestoneId] = [];
                childrenOf[parentMilestoneId].push(t);
            } else {
                unassigned.push(t);
            }
        }

        // ── Build rows: milestone sandwich (phase → children → diamond) ──
        for (var m = 0; m < milestoneOrder.length; m++) {
            var mid = milestoneOrder[m];
            var ms = milestones[mid];

            // Phase bar
            rows.push(toRow(ms, projectStart));

            // Child tasks
            var children = childrenOf[mid] || [];
            for (var c = 0; c < children.length; c++) {
                rows.push(toRow(children[c], projectStart));
            }

            // Closing diamond
            rows.push(toDiamondRow(ms, projectStart));
        }

        // ── Unassigned tasks at the bottom ───────────────────────────────
        for (var i = 0; i < unassigned.length; i++) {
            rows.push(toRow(unassigned[i], projectStart));
        }

        // ── Populate children arrays on phase rows ───────────────────────
        var phaseMap = {};
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].type === 'phase') phaseMap[rows[i].id] = rows[i];
        }
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].parentId && phaseMap[rows[i].parentId]) {
                if (!phaseMap[rows[i].parentId].children) {
                    phaseMap[rows[i].parentId].children = [];
                }
                phaseMap[rows[i].parentId].children.push(rows[i].id);
            }
        }

        return { rows: rows, projectStart: projectStart };
    }

    /**
     * Flat conversion for program/portfolio timelines.
     * No milestone sandwich grouping — every item is a standalone bar.
     * Dependencies render as arrows but don't create parent/child nesting.
     */
    function convertTasksFlat(tasks) {
        var projectStart = computeProjectStart(tasks);
        var rows = [];

        for (var i = 0; i < tasks.length; i++) {
            var t = tasks[i];
            var startDate = parseDate(t.start);
            var endDate = parseDate(t.end);
            var dayOffset = daysBetween(projectStart, startDate);
            var duration = Math.max(7, daysBetween(startDate, endDate));
            var progress = parseFloat(t.progress) || 0;

            var deps = [];
            if (t.dependencies && t.dependencies !== '' && t.dependencies !== '-1') {
                deps = String(t.dependencies).split(',').map(function (d) { return d.trim(); }).filter(Boolean);
            }

            var status = 'upcoming';
            if (progress >= 100) status = 'done';
            else if (progress > 0) status = 'active';

            rows.push({
                id: String(t.id),
                type: 'task',
                name: t.name || '',
                start: dayOffset,
                duration: duration,
                progress: progress,
                depth: 0,
                parentId: null,
                collapsed: false,
                hidden: false,
                status: status,
                dependencies: deps,
                phaseColor: resolvePhaseColor(t.bg_color),
                _ltId: t.id,
                _ltType: t.type,
                _ltProjectName: t.projectName || null,
                _ltSortIndex: t.sortIndex,
                _ltThumbnail: t.thumbnail || null,
            });
        }

        return { rows: rows, projectStart: projectStart };
    }

    // ── Emboss day offset → Leantime date string ─────────────────────────

    function offsetToSQL(dayOffset, projectStart) {
        return toSQL(addDays(projectStart, dayOffset));
    }

    // ── Persistence: wire Emboss events to Leantime APIs ─────────────────

    function saveTicketDates(id, startSQL, endSQL, sortIndex) {
        leantime.ticketsRepository.updateMilestoneDates(id, startSQL, endSQL, sortIndex);
    }

    function saveProjectDates(id, startSQL, endSQL, sortIndex) {
        fetch(leantime.appUrl + '/api/projects', {
            method: 'PATCH',
            body: new URLSearchParams({
                id: id,
                start: startSQL,
                end: endSQL,
                sortIndex: sortIndex,
            }),
            credentials: 'include',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    }

    function saveGanttSort(tasks, apiUrl) {
        var url = apiUrl || leantime.appUrl + '/api/tickets';
        var payload = { action: 'ganttSort', payload: {} };
        for (var i = 0; i < tasks.length; i++) {
            payload.payload[tasks[i].id] = i + 1;
        }

        var params = {};
        params.action = payload.action;
        for (var key in payload.payload) {
            if (payload.payload.hasOwnProperty(key)) {
                params['payload[' + key + ']'] = payload.payload[key];
            }
        }

        fetch(url, {
            method: 'POST',
            body: new URLSearchParams(params),
            credentials: 'include',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    }

    function saveViewSetting(key, mode) {
        var leantimeMode = VIEW_MAP_REVERSE[mode] || mode;
        leantime.usersRepository.updateUserViewSettings(key, leantimeMode);
    }

    // ── Click handler: open modal ────────────────────────────────────────

    function handleClick(row) {
        if (!row) return;

        var rawId = row._ltId || row.id;
        var rawType = row._ltType || row.type;

        // Parse composite IDs (pgm-123, ticket-456)
        var entityId = rawId;
        var entityType = rawType;
        var parts = String(rawId).split('-');
        if (parts.length > 1) {
            if (parts[0] === 'pgm') {
                entityType = 'project';
                entityId = parts[1];
            } else if (parts[0] === 'ticket') {
                entityType = 'ticket';
                entityId = parts[1];
            }
        }

        // Open appropriate modal
        if (entityType === 'milestone' || rawType === 'milestone') {
            leantime.modals.openByUrl('/tickets/editMilestone/' + entityId);
        } else if (entityType === 'project') {
            leantime.modals.openByUrl('/projects/showProject/' + entityId);
        } else {
            leantime.modals.openByUrl('/tickets/showTicket/' + entityId);
        }
    }

    // ── Custom drag layer ───────────────────────────────────────────────
    // Handles three things Emboss core doesn't:
    //   1. Phase bar horizontal drag + edge resize (core skips phase rows)
    //   2. Vertical drag-to-reorder for ALL bar types on the timeline
    //   3. Diamond auto-follow: diamonds stay pinned to their phase's end date

    function initCustomDrag(selector, chart, projectStart, entityType, sortApiUrl) {
        var barsEl = document.querySelector(selector + ' .emboss-bars');
        var bodyEl = document.querySelector(selector + ' .emboss-body');
        if (!barsEl) return;

        // ── Post-render enhancements for phase bars ─────────────────────
        function enhancePhaseBars() {
            var phaseBars = barsEl.querySelectorAll('.emboss-bar-phase');
            var rows = chart.state.rows;

            for (var i = 0; i < phaseBars.length; i++) {
                var bar = phaseBars[i];
                var rowId = bar.dataset.id;
                var row = rows.find(function (r) { return r.id === rowId; });

                // ── Resize handles ───────────────────────────────────────
                if (!bar.querySelector('.emboss-phase-handle')) {
                    var handleL = document.createElement('div');
                    handleL.className = 'emboss-phase-handle emboss-phase-handle-left';
                    var handleR = document.createElement('div');
                    handleR.className = 'emboss-phase-handle emboss-phase-handle-right';
                    bar.appendChild(handleL);
                    bar.appendChild(handleR);
                }

                // ── Progress fill ────────────────────────────────────────
                // Compute aggregate progress from children (tasks only, not diamonds)
                if (row) {
                    var children = rows.filter(function (r) {
                        return r.parentId === rowId && r.type !== 'milestone' && !r._ltIsDiamond;
                    });
                    var progress = 0;
                    if (children.length > 0) {
                        var total = 0;
                        for (var j = 0; j < children.length; j++) {
                            total += (parseFloat(children[j].progress) || 0);
                        }
                        progress = Math.round(total / children.length);
                    } else {
                        progress = parseFloat(row.progress) || 0;
                    }

                    // Store computed progress on the phase row
                    row.progress = progress;

                    // Sync progress to the diamond row too, so Emboss renders its fill
                    var diamondId = rowId + '-end';
                    var diamond = rows.find(function (r) { return r.id === diamondId; });
                    if (diamond) {
                        diamond.progress = progress;
                        diamond.status = progress >= 100 ? 'done' : progress > 0 ? 'active' : 'upcoming';
                    }

                    // Create or update fill element on the phase bar
                    var fill = bar.querySelector('.emboss-phase-fill');
                    if (!fill) {
                        fill = document.createElement('div');
                        fill.className = 'emboss-phase-fill';
                        bar.insertBefore(fill, bar.firstChild);
                    }
                    fill.style.width = Math.min(100, Math.max(0, progress)) + '%';

                    // Update progress text on the label
                    var label = bar.querySelector('.emboss-bar-label');
                    if (label && row.name) {
                        var pctText = progress > 0 && progress < 100 ? ' · ' + progress + '%' : progress >= 100 ? ' · Done' : '';
                        label.textContent = row.name + pctText;
                    }
                }
            }
        }
        enhancePhaseBars();
        chart.on('onRender', enhancePhaseBars);

        // ── Diamond sync: update diamond position when its phase moves ───
        function syncDiamondToPhase(phaseRow) {
            var diamondId = phaseRow.id + '-end';
            var diamond = chart.state.rows.find(function (r) { return r.id === diamondId; });
            if (diamond) {
                diamond.start = phaseRow.start + phaseRow.duration;
            }
        }

        // ── Helpers ──────────────────────────────────────────────────────
        function getRowHeight() {
            return chart.state.scale.rowHeight || 40;
        }

        function getVisibleRows() {
            return chart.state.rows.filter(function (r) { return !r.hidden; });
        }

        function yToRowIndex(y) {
            var rh = getRowHeight();
            var idx = Math.floor(y / rh);
            var visible = getVisibleRows();
            return Math.max(0, Math.min(idx, visible.length - 1));
        }

        // ── Drop indicator line ──────────────────────────────────────────
        var dropLine = document.createElement('div');
        dropLine.className = 'emboss-reorder-indicator';
        dropLine.style.cssText = 'position:absolute;left:0;right:0;height:2px;background:var(--accent1,#004766);z-index:50;pointer-events:none;display:none;';
        barsEl.appendChild(dropLine);

        // ── State ────────────────────────────────────────────────────────
        var drag = null;
        var MOVE_THRESHOLD = 5;      // px dead zone before any drag starts
        var REORDER_RATIO = 1.8;     // vertical must exceed horizontal by this ratio to reorder

        // ── Mousedown on any bar ─────────────────────────────────────────
        barsEl.addEventListener('mousedown', function (e) {
            if (e.button !== 0) return;

            var handle = e.target.closest('.emboss-phase-handle');
            var barEl = e.target.closest('.emboss-bar[data-id]');
            if (!barEl) return;

            var isPhase = barEl.classList.contains('emboss-bar-phase');
            var rowId = barEl.dataset.id;
            var row = chart.state.rows.find(function (r) { return r.id === rowId; });
            if (!row) return;

            // Diamonds are not independently draggable — they follow their phase
            if (row._ltIsDiamond) return;

            // Determine initial drag intent
            var intent = 'pending';
            if (isPhase && handle) {
                if (handle.classList.contains('emboss-phase-handle-left')) intent = 'resize-left';
                else if (handle.classList.contains('emboss-phase-handle-right')) intent = 'resize-right';
            }

            // For phase bars and resize, block Emboss core immediately
            if (isPhase || intent !== 'pending') {
                e.preventDefault();
                e.stopPropagation();
            }

            var visible = getVisibleRows();
            var rowIndex = visible.indexOf(row);

            drag = {
                row: row,
                barEl: barEl,
                isPhase: isPhase,
                intent: intent,
                startX: e.clientX,
                startY: e.clientY,
                originalStart: row.start,
                originalDuration: row.duration,
                dayWidth: chart.state.scale.dayWidth,
                rowHeight: getRowHeight(),
                originalIndex: rowIndex,
                _newStart: null,
                _newDuration: null,
                _newIndex: null,
                ghost: null,
                coreCancelled: false,
            };
        });

        document.addEventListener('mousemove', function (e) {
            if (!drag) return;

            var dx = e.clientX - drag.startX;
            var dy = e.clientY - drag.startY;
            var absDx = Math.abs(dx);
            var absDy = Math.abs(dy);

            // ── Resolve pending intent ───────────────────────────────────
            if (drag.intent === 'pending') {
                var totalMove = Math.max(absDx, absDy);
                if (totalMove < MOVE_THRESHOLD) return; // Still in dead zone

                // Vertical reorder requires strongly vertical movement
                if (absDy > absDx * REORDER_RATIO && absDy > MOVE_THRESHOLD) {
                    drag.intent = 'reorder';
                    // For non-phase bars, cancel Emboss core's drag
                    if (!drag.isPhase && !drag.coreCancelled) {
                        drag.coreCancelled = true;
                        drag.barEl.style.opacity = '';
                        var coreGhost = barsEl.querySelector('.emboss-bar-ghost');
                        if (coreGhost) coreGhost.remove();
                    }
                } else {
                    // Horizontal movement — phase bars we handle, task bars Emboss handles
                    if (drag.isPhase) {
                        drag.intent = 'move-h';
                    } else {
                        // Let Emboss core handle horizontal drag for tasks
                        drag = null;
                        return;
                    }
                }
            }

            e.preventDefault();

            // ── Vertical reorder ─────────────────────────────────────────
            if (drag.intent === 'reorder') {
                if (!drag.ghost) {
                    drag.ghost = drag.barEl.cloneNode(true);
                    drag.ghost.classList.add('emboss-bar-ghost');
                    drag.ghost.style.pointerEvents = 'none';
                    drag.ghost.style.zIndex = '100';
                    barsEl.appendChild(drag.ghost);
                    drag.barEl.style.opacity = '0.3';
                }

                var origTop = drag.originalIndex * drag.rowHeight;
                drag.ghost.style.top = (origTop + dy) + 'px';

                var barsRect = barsEl.getBoundingClientRect();
                var relY = e.clientY - barsRect.top + (bodyEl ? bodyEl.scrollTop : 0);
                var targetIdx = yToRowIndex(relY);
                drag._newIndex = targetIdx;
                dropLine.style.top = (targetIdx * drag.rowHeight) + 'px';
                dropLine.style.display = 'block';
                return;
            }

            // ── Phase horizontal move / resize ───────────────────────────
            var dayDelta = Math.round(dx / drag.dayWidth);

            if (drag.intent === 'move-h') {
                var newStart = drag.originalStart + dayDelta;
                drag.barEl.style.left = (newStart * drag.dayWidth) + 'px';
                drag._newStart = newStart;
                drag._newDuration = drag.originalDuration;
            } else if (drag.intent === 'resize-right') {
                var newDuration = Math.max(1, drag.originalDuration + dayDelta);
                drag.barEl.style.width = (newDuration * drag.dayWidth) + 'px';
                drag._newStart = drag.originalStart;
                drag._newDuration = newDuration;
            } else if (drag.intent === 'resize-left') {
                var newStart2 = drag.originalStart + dayDelta;
                var newDuration2 = Math.max(1, drag.originalDuration - dayDelta);
                drag.barEl.style.left = (newStart2 * drag.dayWidth) + 'px';
                drag.barEl.style.width = (newDuration2 * drag.dayWidth) + 'px';
                drag._newStart = newStart2;
                drag._newDuration = newDuration2;
            }
        });

        document.addEventListener('mouseup', function (e) {
            if (!drag) return;

            // Clean up ghost + indicator
            if (drag.ghost) {
                drag.ghost.remove();
            }
            drag.barEl.style.opacity = '';
            dropLine.style.display = 'none';

            // ── Handle vertical reorder ──────────────────────────────────
            if (drag.intent === 'reorder' && drag._newIndex !== null && drag._newIndex !== drag.originalIndex) {
                var visible = getVisibleRows();
                var row = drag.row;
                var allRows = chart.state.rows;
                var fromAll = allRows.indexOf(row);

                var targetVisible = visible[drag._newIndex];
                var toAll = targetVisible ? allRows.indexOf(targetVisible) : allRows.length - 1;

                allRows.splice(fromAll, 1);
                if (toAll > fromAll) toAll--;
                allRows.splice(toAll, 0, row);

                // Update parent relationships based on new position
                var newParent = null;
                for (var i = toAll - 1; i >= 0; i--) {
                    if (allRows[i].type === 'phase') {
                        newParent = allRows[i];
                        break;
                    }
                    if (allRows[i].depth === 0) break;
                }

                if (row.type !== 'phase') {
                    var oldParentId = row.parentId;
                    var newParentId = newParent ? newParent.id : null;
                    if (oldParentId !== newParentId) {
                        if (oldParentId) {
                            var oldParentRow = allRows.find(function (r) { return r.id === oldParentId; });
                            if (oldParentRow && oldParentRow.children) {
                                oldParentRow.children = oldParentRow.children.filter(function (cid) { return cid !== row.id; });
                            }
                        }
                        row.parentId = newParentId;
                        row.depth = newParentId ? 1 : 0;
                        if (newParent) {
                            if (!newParent.children) newParent.children = [];
                            newParent.children.push(row.id);
                        }
                    }
                }

                var url = sortApiUrl || (entityType === 'project' ? leantime.appUrl + '/api/projects' : null);
                saveGanttSort(allRows, url);
                chart.render();
                drag = null;
                return;
            }

            // ── Handle phase horizontal drag / resize ────────────────────
            if (drag.isPhase && (drag.intent === 'move-h' || drag.intent === 'resize-left' || drag.intent === 'resize-right')) {
                var row = drag.row;
                var newStart = drag._newStart !== null ? drag._newStart : row.start;
                var newDuration = drag._newDuration !== null ? drag._newDuration : row.duration;

                if (newStart !== drag.originalStart || newDuration !== drag.originalDuration) {
                    row.start = newStart;
                    row.duration = newDuration;

                    // Keep the diamond pinned to the phase's end
                    syncDiamondToPhase(row);

                    var startSQL = offsetToSQL(newStart, projectStart);
                    var endSQL = offsetToSQL(newStart + newDuration, projectStart);
                    var rawId = row._ltId || row.id;

                    var parts = String(rawId).split('-');
                    if (parts.length > 1 && parts[0] === 'pgm') {
                        saveProjectDates(parts[1], startSQL, endSQL, 0);
                    } else if (entityType === 'project') {
                        saveProjectDates(rawId, startSQL, endSQL, 0);
                    } else {
                        saveTicketDates(rawId, startSQL, endSQL, 0);
                    }

                    chart.render();
                }
            }

            drag = null;
        });

        // ── Also sync diamonds when Emboss core finishes a task drag ─────
        // (A task drag may not change the phase, but this covers edge cases)
        chart.on('onDragEnd', function (row) {
            if (row.parentId) {
                var phase = chart.state.rows.find(function (r) { return r.id === row.parentId && r.type === 'phase'; });
                if (phase) syncDiamondToPhase(phase);
            }
        });
    }

    // ── Main init function ───────────────────────────────────────────────

    /**
     * @param {string} selector - CSS selector for the container element
     * @param {Array}  leantimeTasks - Array of Leantime task objects (old format)
     * @param {Object} options
     * @param {string} options.viewMode - 'Day'|'Week'|'Month'|'Quarter'
     * @param {boolean} options.readonly - Disable drag/edit
     * @param {string} options.viewSettingKey - Key for persisting view mode ('roadmap', 'projectGantt', 'programsTimeline')
     * @param {string} options.entityType - 'ticket'|'project'|'mixed' (determines save handler)
     * @param {string} options.sortApiUrl - Override URL for sort persistence
     * @returns {EmbossInstance} The Emboss chart instance
     */
    function init(selector, leantimeTasks, options) {
        options = options || {};

        var entityType = options.entityType || 'ticket';

        // Program/portfolio timelines use flat rows (no milestone sandwich)
        var converted = entityType === 'project'
            ? convertTasksFlat(leantimeTasks)
            : convertTasks(leantimeTasks);
        var rows = converted.rows;
        var projectStart = converted.projectStart;

        var viewMode = VIEW_MAP[options.viewMode] || options.viewMode || 'month';
        var readonly = options.readonly || false;
        var viewSettingKey = options.viewSettingKey || 'roadmap';
        var sortApiUrl = options.sortApiUrl || null;

        // Build extensions list
        var isFlat = entityType === 'project';
        var extensions = [
            todayMarker,
            dependencyArrows,
            tooltips,
        ];
        // Phase/milestone extensions only for ticket-based views (roadmap)
        if (!isFlat) {
            extensions.push(phases);
            extensions.push(milestones);
        }

        // Add sidebar + columns for non-readonly views
        // Flat mode (program timeline) gets sidebar but no inline edit or columns
        if (!readonly) {
            extensions.push(sidebar);
            if (!isFlat) {
                extensions.push(inlineEdit);
                extensions.push(columns);
            }
        }

        // Enable vivid mode so phaseColor heuristics apply
        var container = document.querySelector(selector);
        if (container) container.classList.add('emboss-vivid');

        var chart = new Emboss(selector, rows, {
            licenseKey: 'EMB-OCPSA-20301231-4a736e3f',
            view: viewMode,
            density: 'working',
            startDate: projectStart,
            extensions: extensions,
            moveDependencies: true,
        });

        // ── Wire events ──────────────────────────────────────────────────

        // Drag end → persist date changes
        chart.on('onDragEnd', function (row, update) {
            if (readonly) return false; // Cancel the drag
            if (row._ltIsDiamond) return false; // Diamonds are visual-only

            var newStart = update.start !== undefined ? update.start : row.start;
            var newDuration = update.duration !== undefined ? update.duration : row.duration;
            var startSQL = offsetToSQL(newStart, projectStart);
            var endSQL = offsetToSQL(newStart + newDuration, projectStart);

            var rawId = row._ltId || row.id;
            var sortIndex = rows.indexOf(row) + 1;

            // Parse composite IDs
            var parts = String(rawId).split('-');
            if (parts.length > 1 && parts[0] === 'pgm') {
                saveProjectDates(parts[1], startSQL, endSQL, sortIndex);
            } else if (parts.length > 1 && parts[0] === 'ticket') {
                saveTicketDates(parts[1], startSQL, endSQL, sortIndex);
            } else if (entityType === 'project') {
                saveProjectDates(rawId, startSQL, endSQL, sortIndex);
            } else {
                saveTicketDates(rawId, startSQL, endSQL, sortIndex);
            }
        });

        // Click → open modal (covers .emboss-bar elements)
        chart.on('onClick', function (row, event) {
            handleClick(row);
        });

        // Diamonds (.emboss-milestone) and phase bars (.emboss-bar-phase) need direct
        // click handling — diamonds aren't .emboss-bar, and phase bars have pointer-events:none in Emboss core.
        var barsEl = document.querySelector(selector + ' .emboss-bars');
        if (barsEl) {
            barsEl.addEventListener('click', function (e) {
                var el = e.target.closest('.emboss-milestone[data-id], .emboss-bar-phase[data-id]');
                if (el) {
                    var rowId = el.dataset.id;
                    var row = chart.state.rows.find(function (r) { return r.id === rowId; });
                    if (row) handleClick(row);
                }
            });
        }

        // ── Custom drag: phase h-drag/resize + vertical reorder for all bars
        if (!readonly) {
            initCustomDrag(selector, chart, projectStart, entityType, sortApiUrl);
        }

        // View change → persist preference
        chart.on('onViewChange', function (view) {
            saveViewSetting(viewSettingKey, view);
        });

        // Row reorder → persist sort
        chart.on('onRowReorder', function (rowId, newIndex) {
            var url = sortApiUrl || (entityType === 'project' ? leantime.appUrl + '/api/projects' : null);
            saveGanttSort(chart.rows, url);
        });

        // ── Sidebar ↔ body scroll sync (reverse direction) ──────────────
        var sidebarEl = document.querySelector(selector + ' .emboss-sidebar');
        var bodyEl = document.querySelector(selector + ' .emboss-body');
        if (sidebarEl && bodyEl) {
            sidebarEl.addEventListener('scroll', function () {
                bodyEl.scrollTop = sidebarEl.scrollTop;
            });
        }

        // ── View mode control (dropdown) ─────────────────────────────────

        var ganttTimeControl = document.querySelector('#ganttTimeControl');
        if (ganttTimeControl) {
            ganttTimeControl.addEventListener('click', function (e) {
                var link = e.target.closest('a');
                if (!link) return;

                var mode = link.getAttribute('data-value');
                var embossMode = VIEW_MAP[mode] || mode.toLowerCase();
                chart.setView(embossMode);

                ganttTimeControl.querySelectorAll('a').forEach(function (a) {
                    a.classList.remove('active');
                });
                link.classList.add('active');

                document.querySelectorAll('.viewText').forEach(function (el) {
                    el.textContent = link.textContent.trim();
                });
            });
        }

        return chart;
    }

    // ── Public API ───────────────────────────────────────────────────────

    return {
        init: init,
        convertTasks: convertTasks,
    };

})();
