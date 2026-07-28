{{--
    Milestone chip row for a goal (board + dashboard).
    Expects: $milestones = [{ id, headline, color, percentDone, statusType }, ...]
    The fill is the milestone's OWN color growing with progress — deliberately
    not a status color (status lives on the goal card, never the chips).
    Each chip links to its milestone; stopPropagation keeps the click from
    also opening the goal card behind it.
--}}
@if (! empty($milestones))
    <style>
        .goalMsBoardRow{scrollbar-width:thin;scrollbar-color:#9aa7ad transparent;}
        .goalMsBoardRow::-webkit-scrollbar{height:8px;}
        .goalMsBoardRow::-webkit-scrollbar-thumb{background:#9aa7ad;border-radius:10px;border:2px solid transparent;background-clip:padding-box;}
        .goalMsBoardRow::-webkit-scrollbar-track{background:transparent;}
        .goalMsBoardChip{text-decoration:none;color:inherit;cursor:pointer;transition:border-color .12s;}
        .goalMsBoardChip:hover{border-color:var(--primary-color,#004666)!important;}
    </style>
    <div class="goalMsBoardRow" style="display:flex;gap:6px;overflow-x:auto;padding-bottom:4px;margin-top:6px;">
        @foreach ($milestones as $ms)
            <a href="#/tickets/editMilestone/{{ (int) $ms['id'] }}" onclick="event.stopPropagation();" class="goalMsBoardChip" title="{{ __('links.edit_milestone') }}: {{ $ms['headline'] }}"
               style="position:relative;flex:0 0 auto;min-width:120px;max-width:180px;height:32px;border-radius:8px;border:1px solid var(--main-border-color,#e4e7ec);background:var(--secondary-background,#f2f4f7);overflow:hidden;display:flex;align-items:center;padding:0 9px;">
                <span style="position:absolute;left:0;top:0;bottom:0;width:{{ (int) $ms['percentDone'] }}%;background:{{ $ms['color'] }};opacity:.18;border-right:2px solid {{ $ms['color'] }};"></span>
                <span style="position:relative;z-index:1;flex:1;min-width:0;font-size:11.5px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $ms['headline'] }}</span>
                <span style="position:relative;z-index:1;flex:none;font-size:10px;font-weight:600;opacity:.65;margin-left:5px;">{{ (int) $ms['percentDone'] }}%</span>
            </a>
        @endforeach
    </div>
@endif
