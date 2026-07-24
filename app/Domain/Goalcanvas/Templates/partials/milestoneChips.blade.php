{{--
    Read-only milestone chip row for a goal (board + dashboard).
    Expects: $milestones = [{ id, headline, color, percentDone, statusType }, ...]
    The fill is the milestone's OWN color growing with progress — deliberately
    not a status color (status lives on the goal card, never the chips).
--}}
@if (! empty($milestones))
    <div style="display:flex;gap:6px;overflow-x:auto;padding-bottom:4px;margin-top:6px;">
        @foreach ($milestones as $ms)
            <div style="position:relative;flex:0 0 auto;min-width:120px;max-width:180px;height:32px;border-radius:8px;border:1px solid var(--tertiary-color,#e4e7ec);background:var(--secondary-background,#f2f4f7);overflow:hidden;display:flex;align-items:center;padding:0 9px;">
                <span style="position:absolute;left:0;top:0;bottom:0;width:{{ (int) $ms['percentDone'] }}%;background:{{ $ms['color'] }};opacity:.18;border-right:2px solid {{ $ms['color'] }};"></span>
                <span style="position:relative;z-index:1;flex:1;font-size:11.5px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $ms['headline'] }}</span>
                <span style="position:relative;z-index:1;font-size:10px;font-weight:600;opacity:.65;margin-left:5px;">{{ (int) $ms['percentDone'] }}%</span>
            </div>
        @endforeach
    </div>
@endif
