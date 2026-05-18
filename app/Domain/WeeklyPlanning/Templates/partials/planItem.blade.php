<tr id="plan-item-{{ $item['id'] }}">
    <td>
        <div style="font-size:13px;">
            @if(!empty($item['ticketId']))
            <a href="#/tickets/showTicket/{{ $item['ticketId'] }}"
                style="color:var(--primary-font-color); text-decoration:none;"
                onmouseover="this.style.color='var(--accent1)'"
                onmouseout="this.style.color='var(--primary-font-color)'"
                preload="mouseover">
                <i class="fa fa-ticket" style="opacity:.4; margin-right:4px; font-size:11px;"></i>
                {{ $item['ticketHeadline'] ?? __('weeklyplanning.text.task_deleted') }}
            </a>
            @else
            <span>{{ $item['expectedOutcome'] ?? '—' }}</span>
            @endif
        </div>
        @if(!empty($item['completionReason']))
        <div style="margin-top:5px; padding:5px 10px; border-radius:4px; font-size:11px;
                    background:var(--layered-background); border-left:3px solid var(--accent2); color:var(--grey);">
            <strong>{{ __('weeklyplanning.labels.reason') }}:</strong> {{ $item['completionReason'] }}
            @if(!empty($item['supportNeeded']))
            <br><strong>{{ __('weeklyplanning.labels.support_needed') }}:</strong> {{ $item['supportNeeded'] }}
            @endif
            @if(!empty($item['newDueDate']))
            <br><strong>{{ __('weeklyplanning.labels.new_due_date') }}:</strong>
            {{ \Carbon\Carbon::parse($item['newDueDate'])->format('d M Y') }}
            @endif
        </div>
        @endif
    </td>
    <td>
        @php
            $statusKey = $item['status'] ?? 'not_started';
            $chipStyle = match($statusKey) {
                'completed'     => 'background:rgba(34,197,94,.15); color:#22c55e;',
                'in_progress'   => 'background:rgba(74,158,255,.15); color:#4a9eff;',
                'blocked'       => 'background:rgba(249,115,22,.15); color:#f97316;',
                'not_completed' => 'background:rgba(239,68,68,.15); color:#ef4444;',
                default         => 'background:rgba(150,150,150,.1); color:var(--grey);',
            };
        @endphp
        @if(isset($isTeamLead) && $isTeamLead)
        <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; {{ $chipStyle }}">
            {{ __('weeklyplanning.status.'.$statusKey) }}
        </span>
        @else
        <div hx-get="{{ BASE_URL }}/hx/weekly-planning/statusUpdate/get?itemId={{ $item['id'] }}"
            hx-trigger="load" hx-swap="innerHTML">
            <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; {{ $chipStyle }}">
                {{ __('weeklyplanning.status.'.$statusKey) }}
            </span>
        </div>
        @endif
    </td>
    @if(isset($isTeamLead) && $isTeamLead)
    <td style="text-align:center;">
        <button style="background:none; border:none; cursor:pointer; color:var(--grey); padding:4px 6px; border-radius:4px; font-size:13px; transition:color .15s;"
            onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='var(--grey)'"
            hx-post="{{ BASE_URL }}/hx/weekly-planning/planItems/remove?itemId={{ $item['id'] }}"
            hx-target="#plan-items-list"
            hx-swap="innerHTML"
            hx-confirm="{{ __('weeklyplanning.text.confirm_remove_task') }}">
            <i class="fa fa-trash"></i>
        </button>
    </td>
    @endif
</tr>
