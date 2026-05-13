<tr id="plan-item-{{ $item['id'] }}">
    <td>
        @if(!empty($item['ticketId']))
        <a href="#/tickets/showTicket/{{ $item['ticketId'] }}" preload="mouseover">
            {{ $item['ticketHeadline'] ?? __('weeklyplanning.text.task_deleted') }}
        </a>
        @else
        <span>{{ $item['expectedOutcome'] ?? '—' }}</span>
        @endif
        @if(!empty($item['completionReason']))
        <div class="tw-mt-xs tw-p-xs tw-rounded tw-text-xs"
            style="background:var(--layered-background); border-left:3px solid var(--accent2);">
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
        @if(isset($isTeamLead) && $isTeamLead)
        {{-- Team Lead sees a read-only badge; only the developer can change their own status --}}
        <span class="label label-{{ match($item['status']) {
                'completed'     => 'success',
                'in_progress'   => 'primary',
                'blocked'       => 'warning',
                'not_completed' => 'danger',
                default         => 'default'
            } }}">
            {{ __('weeklyplanning.status.'.$item['status']) }}
        </span>
        @else
        {{-- Developer gets the interactive HTMX status control --}}
        <div hx-get="{{ BASE_URL }}/hx/weeklyplanning/statusUpdate/get?itemId={{ $item['id'] }}"
            hx-trigger="load"
            hx-swap="innerHTML">
            <span class="label label-{{ match($item['status']) {
                    'completed'     => 'success',
                    'in_progress'   => 'primary',
                    'blocked'       => 'warning',
                    'not_completed' => 'danger',
                    default         => 'default'
                } }}">
                {{ __('weeklyplanning.status.'.$item['status']) }}
            </span>
        </div>
        @endif
    </td>
    @if(isset($isTeamLead) && $isTeamLead)
    <td>
        <button class="btn btn-xs btn-link tw-text-red-500"
            hx-post="{{ BASE_URL }}/hx/weeklyplanning/planItems/remove?itemId={{ $item['id'] }}"
            hx-target="#plan-items-list"
            hx-swap="innerHTML"
            hx-confirm="{{ __('weeklyplanning.text.confirm_remove_task') }}">
            <i class="fa fa-trash"></i>
        </button>
    </td>
    @endif
</tr>
