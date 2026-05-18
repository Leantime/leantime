{{-- This Week's Plan section — embedded in MyToDos widget --}}
@if(!empty($items))
{{-- Reveal the static section label in the parent widget, init datepicker and hover-to-open --}}
<script>
    (function() {
        var lbl = document.getElementById('my-plan-tasks-label');
        if (lbl) lbl.style.display = '';

        jQuery(function() {
            var $section = jQuery('#my-plan-tasks-section');

            // Initialize datepicker on newly HTMX-loaded due-date inputs
            $section.find('.duedates').each(function() {
                if (!jQuery(this).hasClass('hasDatepicker')) {
                    leantime.dateController.initDatePicker(this, null);
                }
            });

            // Show calendar picker on hover over the due-date cell
            $section.find('.due-date-wrapper').on('mouseenter.planDue', function() {
                jQuery(this).find('.duedates').datepicker('show');
            });
        });
    }());
</script>
<style>
    #my-plan-tasks-section .fa-business-time {
        display: none;
    }

    #my-plan-tasks-section .reset-button {
        display: none;
    }

    #my-plan-tasks-section .due-date-container {
        display: none;
    }
</style>
<div id="my-plan-tasks-section">

    <div class="sortable-list" style="padding-left:5px;">
        @foreach($items as $item)
        @if(!empty($item['ticketData']))
        @php
        $ticket = $item['ticketData'];
        $groupKey = 'planTasks';
        @endphp
        @include('widgets::partials.todoItem', [
        'ticket' => $ticket,
        'statusLabels' => $statusLabels,
        'onTheClock' => $onTheClock,
        'tpl' => $tpl,
        'level' => 0,
        'groupKey' => $groupKey,
        ])
        @else
        {{-- Free-text plan item (no linked ticket) --}}
        @php
        $isCompleted = ($item['status'] ?? '') === 'completed';
        @endphp
        <div class="ticketBox priority-border-"
            style="{{ $isCompleted ? 'opacity:0.6;' : '' }}">
            <div class="tw-flex tw-flex-row tw-items-center tw-gap-s tw-py-xs">
                <div class="tw-flex-1 {{ $isCompleted ? 'tw-line-through' : '' }}"
                    style="{{ $isCompleted ? 'color:var(--grey);' : '' }}">
                    <span class="tw-text-sm tw-font-semibold">
                        {{ $item['expectedOutcome'] ?? '—' }}
                    </span>
                </div>
                <div style="min-width:140px;"
                    hx-get="{{ BASE_URL }}/hx/weekly-planning/statusUpdate/get?itemId={{ (int) $item['id'] }}"
                    hx-trigger="load"
                    hx-swap="innerHTML">
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif
