@props([
    'includeTitle' => true,
    'tickets' => [],
    'onTheClock' => false,
    'groupBy' => '',
    'allProjects' => [],
    'allAssignedprojects' => [],
    'projectFilter' => '',
    'hasMoreTickets' => false,
    'nextOffset' => 0,
    'isLoadMore' => true,
])

@php
    // Helper function to count tickets recursively
    if (!function_exists('countTicketsRecursive')) {
        function countTicketsRecursive($tickets) {
            $count = count($tickets);

            foreach ($tickets as $ticket) {
                if (!empty($ticket['children'])) {
                    $count += countTicketsRecursive($ticket['children']);
                }
            }

            return $count;
        }
    }
@endphp

<!-- Just append individual tasks to existing groups -->
@foreach ($tickets as $groupKey => $ticketGroup)
    @if (isset($ticketGroup['tickets']) && count($ticketGroup['tickets']) > 0)
        @foreach ($ticketGroup['tickets'] as $row)
            <div class="additional-task" data-group-key="{{ $groupKey }}" style="display: none;">
                @include('widgets::partials.todoItem', ['ticket' => $row, 'statusLabels' => $statusLabels, 'onTheClock' => $onTheClock, 'tpl' => $tpl, 'level' => 0, 'groupKey' => $groupKey])
            </div>
        @endforeach
    @endif
@endforeach

@if(isset($hasMoreTickets) && $hasMoreTickets === true)
    <!-- Global Load more trigger for infinite scroll -->
    <div id="global-load-more"
         class="load-more-trigger"
         hx-get="{{ BASE_URL }}/widgets/myToDos/loadMore"
         hx-trigger="intersect once"
         hx-target="#global-load-more"
         hx-swap="outerHTML"
         hx-vals='{"offset": {{ $nextOffset }}, "limit": 20, "groupBy": "{{ $groupBy }}", "projectFilter": "{{ $projectFilter }}"}'>
        <div class="tw-text-center tw-py-4">
            <div class="htmx-indicator">
                <div class="indeterminate"></div>
            </div>
            <div class="tw-text-sm tw-text-gray-500">
                {{ __('text.loading_more_tasks') }}...
            </div>
        </div>
    </div>
@endif

<script>
    // Process additional tasks and append to existing groups
    htmx.onLoad(function(content) {
        const additionalTasks = content.querySelectorAll('.additional-task');

        additionalTasks.forEach(function(taskDiv) {
            const groupKey = taskDiv.dataset.groupKey;
            const taskContent = taskDiv.innerHTML;

            // Find existing group's sortable container
            const existingGroup = document.querySelector(`[id*="ticketBox1-${groupKey}-"] .sortable-list`);

            if (existingGroup) {
                // Append task to existing group
                existingGroup.insertAdjacentHTML('beforeend', taskContent);

                // Update counter
                const counter = document.querySelector(`#task-count-${groupKey}`);
                if (counter) {
                    const currentText = counter.textContent;
                    const currentCount = parseInt(currentText.match(/\d+/)[0]) || 0;
                    counter.textContent = `(${currentCount + 1})`;
                }
            } else {
                // Group doesn't exist yet - this shouldn't happen with global pagination,
                // but if it does, we could create a new group here
                console.warn('Group not found for key:', groupKey);
            }
        });

        if (additionalTasks.length > 0) {
            // Re-initialize nested sortable for new content
            jQuery('.sortable-list').nestedSortable();

            // Re-initialize interactive elements
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initStatusDropdown();
            leantime.ticketsController.initDueDateTimePickers();

            // Re-initialize add task buttons
            initAddTaskBtns();
        }
    });
</script>
