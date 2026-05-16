@php
    $dateObj = \DateTime::createFromFormat('Y-m-d', $taskDate) ?: new \DateTime();
    $today   = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    if ($taskDate === $today) {
        $dateLabel = 'Today';
        $dateSub   = $dateObj->format('D, M j');
    } elseif ($taskDate === $yesterday) {
        $dateLabel = 'Yesterday';
        $dateSub   = $dateObj->format('D, M j');
    } else {
        $dateLabel = $dateObj->format('l');
        $dateSub   = $dateObj->format('M j, Y');
    }

    $tasks = $tasks ?? [];
    $totalCount = count($tasks);
    $doneCount  = count(array_filter($tasks, fn($t) => !empty($t['done'])));
@endphp

<div class="notepad-day-header">
    <div class="date">
        {{ $dateLabel }}
        <small>{{ $dateSub }}</small>
    </div>
    @if ($totalCount > 0)
        <small style="color:var(--grey);">{{ $doneCount }} / {{ $totalCount }} done</small>
    @endif
</div>

@foreach ($tasks as $task)
    <div class="notepad-task {{ !empty($task['done']) ? 'done' : '' }}">
        <input type="checkbox"
               data-task-id="{{ $task['id'] }}"
               @if (!empty($task['done'])) checked @endif>
        <input type="text"
               data-task-id="{{ $task['id'] }}"
               value="{{ $task['content'] }}">
        <button type="button" class="delete-btn" data-task-id="{{ $task['id'] }}" title="Delete">
            <i class="fa fa-times"></i>
        </button>
    </div>
@endforeach

@if ($taskDate === date('Y-m-d'))
    <button type="button" class="notepad-add-btn">
        <i class="fa fa-plus"></i> Add task
    </button>
@endif
